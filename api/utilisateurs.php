<?php
/**
 * API Utilisateurs
 * GET /api/utilisateurs.php - Liste des utilisateurs (admin)
 * GET /api/utilisateurs.php?id=X - Détails d'un utilisateur
 * PUT /api/utilisateurs.php?id=X - Modifier un utilisateur (admin)
 * DELETE /api/utilisateurs.php?id=X - Supprimer un utilisateur (admin)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            getUtilisateur($id);
        } else {
            getUtilisateurs();
        }
        break;
    case 'PUT':
        updateUtilisateur($id);
        break;
    case 'DELETE':
        deleteUtilisateur($id);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
}

/**
 * Récupérer tous les utilisateurs (admin seulement)
 */
function getUtilisateurs() {
    requireAdmin();
    
    $db = Database::getInstance()->getConnection();
    
    // Paramètres de pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    // Filtre par rôle
    $roleFilter = isset($_GET['role']) && in_array($_GET['role'], ['utilisateur', 'admin']) 
        ? $_GET['role'] 
        : null;
    
    // Filtre par statut
    $actifFilter = isset($_GET['actif']) ? intval($_GET['actif']) : null;
    
    // Construire la requête
    $where = [];
    $params = [];
    
    if ($roleFilter) {
        $where[] = "role = ?";
        $params[] = $roleFilter;
    }
    
    if ($actifFilter !== null) {
        $where[] = "actif = ?";
        $params[] = $actifFilter;
    }
    
    $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
    
    // Compter le total
    $countSql = "SELECT COUNT(*) as total FROM utilisateurs $whereClause";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Récupérer les utilisateurs
    $sql = "
        SELECT u.id, u.nom, u.email, u.role, u.actif, u.date_inscription,
               (SELECT COUNT(*) FROM annonces WHERE utilisateur_id = u.id) as nombre_annonces
        FROM utilisateurs u
        $whereClause
        ORDER BY u.date_inscription DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $utilisateurs = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'data' => $utilisateurs,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Récupérer un utilisateur spécifique
 */
function getUtilisateur($id) {
    $db = Database::getInstance()->getConnection();
    
    // Vérifier les droits
    $currentUserId = getCurrentUserId();
    if (!isAdmin() && $currentUserId != $id) {
        requireAuth();
        jsonResponse(['success' => false, 'message' => 'Accès non autorisé'], 403);
    }
    
    $stmt = $db->prepare("
        SELECT id, nom, email, role, actif, date_inscription 
        FROM utilisateurs 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $utilisateur = $stmt->fetch();
    
    if (!$utilisateur) {
        jsonResponse(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
    }
    
    // Récupérer les annonces de l'utilisateur
    $stmt = $db->prepare("
        SELECT id, titre, prix, statut, date_publication 
        FROM annonces 
        WHERE utilisateur_id = ?
        ORDER BY date_publication DESC
        LIMIT 10
    ");
    $stmt->execute([$id]);
    $annonces = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'data' => [
            'utilisateur' => $utilisateur,
            'annonces_recentes' => $annonces
        ]
    ]);
}

/**
 * Modifier un utilisateur (admin: tous, utilisateur: soi-même)
 */
function updateUtilisateur($id) {
    requireAuth();
    
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID utilisateur requis'], 400);
    }
    
    $currentUserId = getCurrentUserId();
    
    // Un utilisateur ne peut modifier que son propre profil
    if (!isAdmin() && $currentUserId != $id) {
        jsonResponse(['success' => false, 'message' => 'Accès non autorisé'], 403);
    }
    
    $db = Database::getInstance()->getConnection();
    $data = getJsonInput();
    
    // Vérifier que l'utilisateur existe
    $stmt = $db->prepare("SELECT id, role FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
    }
    
    $updates = [];
    $params = [];
    
    // Champs modifiables par tous
    if (isset($data['nom'])) {
        $updates[] = "nom = ?";
        $params[] = sanitize($data['nom']);
    }
    
    if (isset($data['email'])) {
        if (!validateEmail($data['email'])) {
            jsonResponse(['success' => false, 'message' => 'Email invalide'], 400);
        }
        // Vérifier unicité
        $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $stmt->execute([strtolower($data['email']), $id]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Email déjà utilisé'], 409);
        }
        $updates[] = "email = ?";
        $params[] = strtolower($data['email']);
    }
    
    if (isset($data['mot_de_passe']) && !empty($data['mot_de_passe'])) {
        if (strlen($data['mot_de_passe']) < 6) {
            jsonResponse(['success' => false, 'message' => 'Mot de passe trop court'], 400);
        }
        $updates[] = "mot_de_passe = ?";
        $params[] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
    }
    
    // Champs modifiables uniquement par l'admin
    if (isAdmin()) {
        if (isset($data['role']) && in_array($data['role'], ['utilisateur', 'admin'])) {
            // Ne pas permettre de retirer le dernier admin
            if ($data['role'] === 'utilisateur' && $user['role'] === 'admin') {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM utilisateurs WHERE role = 'admin'");
                $stmt->execute();
                if ($stmt->fetch()['count'] <= 1) {
                    jsonResponse(['success' => false, 'message' => 'Impossible de retirer le dernier administrateur'], 400);
                }
            }
            $updates[] = "role = ?";
            $params[] = $data['role'];
        }
        
        if (isset($data['actif'])) {
            // Ne pas permettre de désactiver le dernier admin
            if (!$data['actif'] && $user['role'] === 'admin') {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM utilisateurs WHERE role = 'admin' AND actif = 1");
                $stmt->execute();
                if ($stmt->fetch()['count'] <= 1) {
                    jsonResponse(['success' => false, 'message' => 'Impossible de désactiver le dernier administrateur'], 400);
                }
            }
            $updates[] = "actif = ?";
            $params[] = $data['actif'] ? 1 : 0;
        }
    }
    
    if (empty($updates)) {
        jsonResponse(['success' => false, 'message' => 'Aucune donnée à modifier'], 400);
    }
    
    $params[] = $id;
    $sql = "UPDATE utilisateurs SET " . implode(', ', $updates) . " WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    logAction('modification_utilisateur', "Utilisateur #$id modifié");
    
    jsonResponse([
        'success' => true,
        'message' => 'Utilisateur mis à jour avec succès'
    ]);
}

/**
 * Supprimer un utilisateur (admin seulement)
 */
function deleteUtilisateur($id) {
    requireAdmin();
    
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID utilisateur requis'], 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Vérifier que l'utilisateur existe
    $stmt = $db->prepare("SELECT id, role FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
    }
    
    // Ne pas permettre de supprimer un admin
    if ($user['role'] === 'admin') {
        jsonResponse(['success' => false, 'message' => 'Impossible de supprimer un administrateur'], 403);
    }
    
    // Supprimer l'utilisateur (cascade supprimera les annonces)
    $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    
    logAction('suppression_utilisateur', "Utilisateur #$id supprimé");
    
    jsonResponse([
        'success' => true,
        'message' => 'Utilisateur supprimé avec succès'
    ]);
}
