<?php
/**
 * API Annonces
 * GET /api/annonces.php - Liste des annonces
 * GET /api/annonces.php?id=X - Détails d'une annonce
 * POST /api/annonces.php - Créer une annonce
 * PUT /api/annonces.php?id=X - Modifier une annonce
 * DELETE /api/annonces.php?id=X - Supprimer une annonce
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
            getAnnonce($id);
        } else {
            getAnnonces();
        }
        break;
    case 'POST':
        createAnnonce();
        break;
    case 'PUT':
        updateAnnonce($id);
        break;
    case 'DELETE':
        deleteAnnonce($id);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
}

/**
 * Récupérer toutes les annonces (avec filtres et pagination)
 */
function getAnnonces() {
    $db = Database::getInstance()->getConnection();
    
    // Paramètres de pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    // Paramètres de filtrage
    $categorie = isset($_GET['categorie']) ? intval($_GET['categorie']) : null;
    $recherche = isset($_GET['q']) ? trim($_GET['q']) : null;
    $prixMin = isset($_GET['prix_min']) ? floatval($_GET['prix_min']) : null;
    $prixMax = isset($_GET['prix_max']) ? floatval($_GET['prix_max']) : null;
    $utilisateur = isset($_GET['utilisateur']) ? intval($_GET['utilisateur']) : null;
    
    // Par défaut, afficher uniquement les annonces actives (sauf pour admin)
    $statut = 'active';
    if (isAdmin() && isset($_GET['statut'])) {
        $statut = $_GET['statut'];
    }
    
    // Tri
    $triOptions = ['date_publication', 'prix', 'titre'];
    $tri = isset($_GET['tri']) && in_array($_GET['tri'], $triOptions) ? $_GET['tri'] : 'date_publication';
    $ordre = isset($_GET['ordre']) && strtoupper($_GET['ordre']) === 'ASC' ? 'ASC' : 'DESC';
    
    // Construire la requête
    $where = [];
    $params = [];
    
    if ($statut !== 'all') {
        $where[] = "a.statut = ?";
        $params[] = $statut;
    }
    
    if ($categorie) {
        $where[] = "a.categorie_id = ?";
        $params[] = $categorie;
    }
    
    if ($recherche) {
        $where[] = "(a.titre LIKE ? OR a.description LIKE ?)";
        $searchTerm = "%$recherche%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($prixMin !== null) {
        $where[] = "a.prix >= ?";
        $params[] = $prixMin;
    }
    
    if ($prixMax !== null) {
        $where[] = "a.prix <= ?";
        $params[] = $prixMax;
    }
    
    if ($utilisateur) {
        $where[] = "a.utilisateur_id = ?";
        $params[] = $utilisateur;
    }
    
    $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
    
    // Compter le total
    $countSql = "SELECT COUNT(*) as total FROM annonces a $whereClause";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Récupérer les annonces
    $sql = "
        SELECT a.id, a.titre, a.description, a.prix, a.image, a.statut,
               a.date_publication, a.date_modification,
               u.id as utilisateur_id, u.nom as utilisateur_nom,
               c.id as categorie_id, c.nom as categorie_nom
        FROM annonces a
        LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id
        LEFT JOIN categories c ON a.categorie_id = c.id
        $whereClause
        ORDER BY a.$tri $ordre
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $annonces = $stmt->fetchAll();
    
    // Formater les résultats
    $formattedAnnonces = array_map(function($a) {
        return [
            'id' => $a['id'],
            'titre' => $a['titre'],
            'description' => $a['description'],
            'prix' => $a['prix'],
            'image' => $a['image'],
            'statut' => $a['statut'],
            'date_publication' => $a['date_publication'],
            'date_modification' => $a['date_modification'],
            'utilisateur' => [
                'id' => $a['utilisateur_id'],
                'nom' => $a['utilisateur_nom']
            ],
            'categorie' => $a['categorie_id'] ? [
                'id' => $a['categorie_id'],
                'nom' => $a['categorie_nom']
            ] : null
        ];
    }, $annonces);
    
    jsonResponse([
        'success' => true,
        'data' => $formattedAnnonces,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ],
        'filtres' => [
            'categorie' => $categorie,
            'recherche' => $recherche,
            'prix_min' => $prixMin,
            'prix_max' => $prixMax
        ]
    ]);
}

/**
 * Récupérer une annonce spécifique
 */
function getAnnonce($id) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT a.*, 
               u.id as utilisateur_id, u.nom as utilisateur_nom, u.email as utilisateur_email,
               c.id as categorie_id, c.nom as categorie_nom
        FROM annonces a
        LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id
        LEFT JOIN categories c ON a.categorie_id = c.id
        WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    $annonce = $stmt->fetch();
    
    if (!$annonce) {
        jsonResponse(['success' => false, 'message' => 'Annonce non trouvée'], 404);
    }
    
    // Vérifier l'accès si l'annonce n'est pas active
    if ($annonce['statut'] !== 'active' && !isAdmin() && getCurrentUserId() != $annonce['utilisateur_id']) {
        jsonResponse(['success' => false, 'message' => 'Annonce non disponible'], 403);
    }
    
    jsonResponse([
        'success' => true,
        'data' => [
            'id' => $annonce['id'],
            'titre' => $annonce['titre'],
            'description' => $annonce['description'],
            'prix' => $annonce['prix'],
            'image' => $annonce['image'],
            'statut' => $annonce['statut'],
            'date_publication' => $annonce['date_publication'],
            'date_modification' => $annonce['date_modification'],
            'utilisateur' => [
                'id' => $annonce['utilisateur_id'],
                'nom' => $annonce['utilisateur_nom'],
                'email' => $annonce['utilisateur_email']
            ],
            'categorie' => $annonce['categorie_id'] ? [
                'id' => $annonce['categorie_id'],
                'nom' => $annonce['categorie_nom']
            ] : null
        ]
    ]);
}

/**
 * Créer une nouvelle annonce
 */
function createAnnonce() {
    requireAuth();
    
    $db = Database::getInstance()->getConnection();
    $data = getJsonInput();
    
    // Validation
    $errors = [];
    if (empty($data['titre'])) $errors[] = 'Le titre est requis';
    if (empty($data['description'])) $errors[] = 'La description est requise';
    if (strlen($data['titre'] ?? '') > 200) $errors[] = 'Le titre ne peut pas dépasser 200 caractères';
    
    if (!empty($errors)) {
        jsonResponse(['success' => false, 'message' => 'Erreurs de validation', 'errors' => $errors], 400);
    }
    
    // Vérifier la catégorie si fournie
    $categorieId = null;
    if (!empty($data['categorie_id'])) {
        $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
        $stmt->execute([$data['categorie_id']]);
        if (!$stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Catégorie invalide'], 400);
        }
        $categorieId = $data['categorie_id'];
    }
    
    // Préparer les données
    $titre = sanitize($data['titre']);
    $description = sanitize($data['description']);
    $prix = isset($data['prix']) ? floatval($data['prix']) : null;
    $image = isset($data['image']) ? sanitize($data['image']) : null;
    $utilisateurId = getCurrentUserId();
    
    // Insérer l'annonce
    $stmt = $db->prepare("
        INSERT INTO annonces (utilisateur_id, titre, description, categorie_id, prix, image)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$utilisateurId, $titre, $description, $categorieId, $prix, $image]);
    
    $annonceId = $db->lastInsertId();
    
    logAction('creation_annonce', "Annonce #$annonceId créée: $titre");
    
    jsonResponse([
        'success' => true,
        'message' => 'Annonce créée avec succès',
        'data' => [
            'id' => $annonceId,
            'titre' => $titre
        ]
    ], 201);
}

/**
 * Modifier une annonce
 */
function updateAnnonce($id) {
    requireAuth();
    
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID annonce requis'], 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Vérifier que l'annonce existe
    $stmt = $db->prepare("SELECT utilisateur_id, statut FROM annonces WHERE id = ?");
    $stmt->execute([$id]);
    $annonce = $stmt->fetch();
    
    if (!$annonce) {
        jsonResponse(['success' => false, 'message' => 'Annonce non trouvée'], 404);
    }
    
    // Vérifier les droits (propriétaire ou admin)
    if (!isAdmin() && getCurrentUserId() != $annonce['utilisateur_id']) {
        jsonResponse(['success' => false, 'message' => 'Non autorisé à modifier cette annonce'], 403);
    }
    
    $data = getJsonInput();
    $updates = [];
    $params = [];
    
    // Champs modifiables
    if (isset($data['titre'])) {
        if (strlen($data['titre']) > 200) {
            jsonResponse(['success' => false, 'message' => 'Titre trop long'], 400);
        }
        $updates[] = "titre = ?";
        $params[] = sanitize($data['titre']);
    }
    
    if (isset($data['description'])) {
        $updates[] = "description = ?";
        $params[] = sanitize($data['description']);
    }
    
    if (isset($data['prix'])) {
        $updates[] = "prix = ?";
        $params[] = floatval($data['prix']);
    }
    
    if (isset($data['categorie_id'])) {
        if ($data['categorie_id']) {
            $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
            $stmt->execute([$data['categorie_id']]);
            if (!$stmt->fetch()) {
                jsonResponse(['success' => false, 'message' => 'Catégorie invalide'], 400);
            }
        }
        $updates[] = "categorie_id = ?";
        $params[] = $data['categorie_id'] ?: null;
    }
    
    if (isset($data['image'])) {
        $updates[] = "image = ?";
        $params[] = $data['image'] ? sanitize($data['image']) : null;
    }
    
    // Seul l'admin peut changer le statut
    if (isAdmin() && isset($data['statut']) && in_array($data['statut'], ['active', 'inactive', 'supprimee'])) {
        $updates[] = "statut = ?";
        $params[] = $data['statut'];
    }
    
    if (empty($updates)) {
        jsonResponse(['success' => false, 'message' => 'Aucune donnée à modifier'], 400);
    }
    
    // Ajouter la date de modification
    $updates[] = "date_modification = CURRENT_TIMESTAMP";
    
    $params[] = $id;
    $sql = "UPDATE annonces SET " . implode(', ', $updates) . " WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    logAction('modification_annonce', "Annonce #$id modifiée");
    
    jsonResponse([
        'success' => true,
        'message' => 'Annonce mise à jour avec succès'
    ]);
}

/**
 * Supprimer une annonce
 */
function deleteAnnonce($id) {
    requireAuth();
    
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID annonce requis'], 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Vérifier que l'annonce existe
    $stmt = $db->prepare("SELECT utilisateur_id FROM annonces WHERE id = ?");
    $stmt->execute([$id]);
    $annonce = $stmt->fetch();
    
    if (!$annonce) {
        jsonResponse(['success' => false, 'message' => 'Annonce non trouvée'], 404);
    }
    
    // Vérifier les droits (propriétaire ou admin)
    if (!isAdmin() && getCurrentUserId() != $annonce['utilisateur_id']) {
        jsonResponse(['success' => false, 'message' => 'Non autorisé à supprimer cette annonce'], 403);
    }
    
    // Supprimer l'annonce
    $stmt = $db->prepare("DELETE FROM annonces WHERE id = ?");
    $stmt->execute([$id]);
    
    logAction('suppression_annonce', "Annonce #$id supprimée");
    
    jsonResponse([
        'success' => true,
        'message' => 'Annonce supprimée avec succès'
    ]);
}
