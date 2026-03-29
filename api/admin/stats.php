<?php
/**
 * API Statistiques Admin
 * GET /api/admin/stats.php - Statistiques générales
 * GET /api/admin/stats.php?type=categories - Stats par catégorie
 * GET /api/admin/stats.php?type=utilisateurs - Stats utilisateurs actifs
 * GET /api/admin/stats.php?type=historique - Historique des actions
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

requireAdmin();

$type = $_GET['type'] ?? 'general';

switch ($type) {
    case 'general':
        getStatsGenerales();
        break;
    case 'categories':
        getStatsParCategorie();
        break;
    case 'utilisateurs':
        getUtilisateursActifs();
        break;
    case 'historique':
        getHistorique();
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Type de statistique non reconnu'], 400);
}

/**
 * Statistiques générales du site
 */
function getStatsGenerales() {
    $db = Database::getInstance()->getConnection();
    
    // Nombre total d'utilisateurs
    $stmt = $db->query("SELECT COUNT(*) as total FROM utilisateurs");
    $totalUtilisateurs = $stmt->fetch()['total'];
    
    // Utilisateurs actifs
    $stmt = $db->query("SELECT COUNT(*) as total FROM utilisateurs WHERE actif = 1");
    $utilisateursActifs = $stmt->fetch()['total'];
    
    // Nombre total d'annonces
    $stmt = $db->query("SELECT COUNT(*) as total FROM annonces");
    $totalAnnonces = $stmt->fetch()['total'];
    
    // Annonces par statut
    $stmt = $db->query("
        SELECT statut, COUNT(*) as count 
        FROM annonces 
        GROUP BY statut
    ");
    $annoncesParStatut = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Annonces aujourd'hui
    $stmt = $db->query("
        SELECT COUNT(*) as total 
        FROM annonces 
        WHERE date(date_publication) = date('now')
    ");
    $annoncesAujourdhui = $stmt->fetch()['total'];
    
    // Annonces cette semaine
    $stmt = $db->query("
        SELECT COUNT(*) as total 
        FROM annonces 
        WHERE date(date_publication) >= date('now', '-7 days')
    ");
    $annoncesSemaine = $stmt->fetch()['total'];
    
    // Inscriptions aujourd'hui
    $stmt = $db->query("
        SELECT COUNT(*) as total 
        FROM utilisateurs 
        WHERE date(date_inscription) = date('now')
    ");
    $inscriptionsAujourdhui = $stmt->fetch()['total'];
    
    // Nombre de catégories
    $stmt = $db->query("SELECT COUNT(*) as total FROM categories");
    $totalCategories = $stmt->fetch()['total'];
    
    jsonResponse([
        'success' => true,
        'data' => [
            'utilisateurs' => [
                'total' => $totalUtilisateurs,
                'actifs' => $utilisateursActifs,
                'inscriptions_aujourdhui' => $inscriptionsAujourdhui
            ],
            'annonces' => [
                'total' => $totalAnnonces,
                'actives' => $annoncesParStatut['active'] ?? 0,
                'inactives' => $annoncesParStatut['inactive'] ?? 0,
                'supprimees' => $annoncesParStatut['supprimee'] ?? 0,
                'aujourdhui' => $annoncesAujourdhui,
                'cette_semaine' => $annoncesSemaine
            ],
            'categories' => $totalCategories
        ]
    ]);
}

/**
 * Statistiques par catégorie
 */
function getStatsParCategorie() {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->query("
        SELECT 
            c.id,
            c.nom,
            COUNT(a.id) as total_annonces,
            SUM(CASE WHEN a.statut = 'active' THEN 1 ELSE 0 END) as annonces_actives,
            AVG(a.prix) as prix_moyen,
            MIN(a.prix) as prix_min,
            MAX(a.prix) as prix_max
        FROM categories c
        LEFT JOIN annonces a ON c.id = a.categorie_id
        GROUP BY c.id, c.nom
        ORDER BY total_annonces DESC
    ");
    
    $stats = $stmt->fetchAll();
    
    // Formater les prix
    $stats = array_map(function($s) {
        $s['prix_moyen'] = $s['prix_moyen'] ? round($s['prix_moyen'], 2) : null;
        return $s;
    }, $stats);
    
    jsonResponse([
        'success' => true,
        'data' => $stats
    ]);
}

/**
 * Utilisateurs les plus actifs
 */
function getUtilisateursActifs() {
    $db = Database::getInstance()->getConnection();
    
    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 10;
    
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.nom,
            u.email,
            u.role,
            u.date_inscription,
            COUNT(a.id) as nombre_annonces,
            SUM(CASE WHEN a.statut = 'active' THEN 1 ELSE 0 END) as annonces_actives,
            MAX(a.date_publication) as derniere_annonce
        FROM utilisateurs u
        LEFT JOIN annonces a ON u.id = a.utilisateur_id
        WHERE u.actif = 1
        GROUP BY u.id, u.nom, u.email, u.role, u.date_inscription
        ORDER BY nombre_annonces DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    
    $utilisateurs = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'data' => $utilisateurs
    ]);
}

/**
 * Historique des actions
 */
function getHistorique() {
    $db = Database::getInstance()->getConnection();
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 50;
    $offset = ($page - 1) * $limit;
    
    // Filtre par action
    $actionFilter = isset($_GET['action']) ? $_GET['action'] : null;
    
    // Filtre par utilisateur
    $utilisateurFilter = isset($_GET['utilisateur']) ? intval($_GET['utilisateur']) : null;
    
    $where = [];
    $params = [];
    
    if ($actionFilter) {
        $where[] = "h.action LIKE ?";
        $params[] = "%$actionFilter%";
    }
    
    if ($utilisateurFilter) {
        $where[] = "h.utilisateur_id = ?";
        $params[] = $utilisateurFilter;
    }
    
    $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
    
    // Compter le total
    $countSql = "SELECT COUNT(*) as total FROM historique h $whereClause";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Récupérer l'historique
    $sql = "
        SELECT h.*, u.nom as utilisateur_nom, u.email as utilisateur_email
        FROM historique h
        LEFT JOIN utilisateurs u ON h.utilisateur_id = u.id
        $whereClause
        ORDER BY h.date_action DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $historique = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'data' => $historique,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}
