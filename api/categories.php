<?php
/**
 * API Catégories
 * GET /api/categories.php - Liste des catégories
 * GET /api/categories.php?id=X - Détails d'une catégorie
 * POST /api/categories.php - Créer une catégorie (admin)
 * PUT /api/categories.php?id=X - Modifier une catégorie (admin)
 * DELETE /api/categories.php?id=X - Supprimer une catégorie (admin)
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
            getCategorie($id);
        } else {
            getCategories();
        }
        break;
    case 'POST':
        createCategorie();
        break;
    case 'PUT':
        updateCategorie($id);
        break;
    case 'DELETE':
        deleteCategorie($id);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
}

/**
 * Récupérer toutes les catégories
 */
function getCategories() {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM annonces WHERE categorie_id = c.id AND statut = 'active') as nombre_annonces
        FROM categories c
        ORDER BY c.nom ASC
    ");
    
    $categories = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'data' => $categories
    ]);
}

/**
 * Récupérer une catégorie spécifique avec ses annonces
 */
function getCategorie($id) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $categorie = $stmt->fetch();
    
    if (!$categorie) {
        jsonResponse(['success' => false, 'message' => 'Catégorie non trouvée'], 404);
    }
    
    // Récupérer les annonces de la catégorie
    $stmt = $db->prepare("
        SELECT a.id, a.titre, a.prix, a.date_publication, u.nom as utilisateur_nom
        FROM annonces a
        LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id
        WHERE a.categorie_id = ? AND a.statut = 'active'
        ORDER BY a.date_publication DESC
        LIMIT 20
    ");
    $stmt->execute([$id]);
    $annonces = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'data' => [
            'categorie' => $categorie,
            'annonces' => $annonces
        ]
    ]);
}

/**
 * Créer une nouvelle catégorie (admin)
 */
function createCategorie() {
    requireAdmin();
    
    $data = getJsonInput();
    
    if (empty($data['nom'])) {
        jsonResponse(['success' => false, 'message' => 'Le nom est requis'], 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Vérifier l'unicité du nom
    $stmt = $db->prepare("SELECT id FROM categories WHERE LOWER(nom) = LOWER(?)");
    $stmt->execute([$data['nom']]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Cette catégorie existe déjà'], 409);
    }
    
    $stmt = $db->prepare("INSERT INTO categories (nom, description) VALUES (?, ?)");
    $stmt->execute([
        sanitize($data['nom']),
        isset($data['description']) ? sanitize($data['description']) : null
    ]);
    
    $id = $db->lastInsertId();
    
    logAction('creation_categorie', "Catégorie créée: {$data['nom']}");
    
    jsonResponse([
        'success' => true,
        'message' => 'Catégorie créée avec succès',
        'data' => [
            'id' => $id,
            'nom' => $data['nom']
        ]
    ], 201);
}

/**
 * Modifier une catégorie (admin)
 */
function updateCategorie($id) {
    requireAdmin();
    
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID catégorie requis'], 400);
    }
    
    $db = Database::getInstance()->getConnection();
    $data = getJsonInput();
    
    // Vérifier que la catégorie existe
    $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Catégorie non trouvée'], 404);
    }
    
    $updates = [];
    $params = [];
    
    if (isset($data['nom'])) {
        // Vérifier l'unicité
        $stmt = $db->prepare("SELECT id FROM categories WHERE LOWER(nom) = LOWER(?) AND id != ?");
        $stmt->execute([$data['nom'], $id]);
        if ($stmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Ce nom de catégorie existe déjà'], 409);
        }
        $updates[] = "nom = ?";
        $params[] = sanitize($data['nom']);
    }
    
    if (isset($data['description'])) {
        $updates[] = "description = ?";
        $params[] = sanitize($data['description']);
    }
    
    if (empty($updates)) {
        jsonResponse(['success' => false, 'message' => 'Aucune donnée à modifier'], 400);
    }
    
    $params[] = $id;
    $sql = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    logAction('modification_categorie', "Catégorie #$id modifiée");
    
    jsonResponse([
        'success' => true,
        'message' => 'Catégorie mise à jour avec succès'
    ]);
}

/**
 * Supprimer une catégorie (admin)
 */
function deleteCategorie($id) {
    requireAdmin();
    
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID catégorie requis'], 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Vérifier que la catégorie existe
    $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Catégorie non trouvée'], 404);
    }
    
    // Vérifier s'il y a des annonces dans cette catégorie
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM annonces WHERE categorie_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        jsonResponse([
            'success' => false, 
            'message' => "Impossible de supprimer: $count annonce(s) dans cette catégorie"
        ], 400);
    }
    
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    
    logAction('suppression_categorie', "Catégorie #$id supprimée");
    
    jsonResponse([
        'success' => true,
        'message' => 'Catégorie supprimée avec succès'
    ]);
}
