<?php
/**
 * API Modération Admin
 * PUT /api/admin/moderation.php?action=activer&id=X - Activer une annonce
 * PUT /api/admin/moderation.php?action=desactiver&id=X - Désactiver une annonce
 * PUT /api/admin/moderation.php?action=activer_utilisateur&id=X - Activer un compte
 * PUT /api/admin/moderation.php?action=desactiver_utilisateur&id=X - Désactiver un compte
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
}

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    jsonResponse(['success' => false, 'message' => 'ID requis'], 400);
}

switch ($action) {
    case 'activer':
        activerAnnonce($id);
        break;
    case 'desactiver':
        desactiverAnnonce($id);
        break;
    case 'activer_utilisateur':
        activerUtilisateur($id);
        break;
    case 'desactiver_utilisateur':
        desactiverUtilisateur($id);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Action non reconnue'], 400);
}

/**
 * Activer une annonce
 */
function activerAnnonce($id) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT id, titre FROM annonces WHERE id = ?");
    $stmt->execute([$id]);
    $annonce = $stmt->fetch();
    
    if (!$annonce) {
        jsonResponse(['success' => false, 'message' => 'Annonce non trouvée'], 404);
    }
    
    $stmt = $db->prepare("UPDATE annonces SET statut = 'active', date_modification = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$id]);
    
    logAction('moderation_activation_annonce', "Annonce #$id activée: {$annonce['titre']}");
    
    jsonResponse([
        'success' => true,
        'message' => 'Annonce activée avec succès'
    ]);
}

/**
 * Désactiver une annonce
 */
function desactiverAnnonce($id) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT id, titre FROM annonces WHERE id = ?");
    $stmt->execute([$id]);
    $annonce = $stmt->fetch();
    
    if (!$annonce) {
        jsonResponse(['success' => false, 'message' => 'Annonce non trouvée'], 404);
    }
    
    $stmt = $db->prepare("UPDATE annonces SET statut = 'inactive', date_modification = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$id]);
    
    logAction('moderation_desactivation_annonce', "Annonce #$id désactivée: {$annonce['titre']}");
    
    jsonResponse([
        'success' => true,
        'message' => 'Annonce désactivée avec succès'
    ]);
}

/**
 * Activer un compte utilisateur
 */
function activerUtilisateur($id) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT id, nom, email FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $utilisateur = $stmt->fetch();
    
    if (!$utilisateur) {
        jsonResponse(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
    }
    
    $stmt = $db->prepare("UPDATE utilisateurs SET actif = 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    logAction('moderation_activation_utilisateur', "Utilisateur #$id activé: {$utilisateur['email']}");
    
    jsonResponse([
        'success' => true,
        'message' => 'Compte utilisateur activé avec succès'
    ]);
}

/**
 * Désactiver un compte utilisateur
 */
function desactiverUtilisateur($id) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT id, nom, email, role FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    $utilisateur = $stmt->fetch();
    
    if (!$utilisateur) {
        jsonResponse(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
    }
    
    // Ne pas désactiver un admin
    if ($utilisateur['role'] === 'admin') {
        // Vérifier s'il reste d'autres admins actifs
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM utilisateurs WHERE role = 'admin' AND actif = 1 AND id != ?");
        $stmt->execute([$id]);
        if ($stmt->fetch()['count'] < 1) {
            jsonResponse(['success' => false, 'message' => 'Impossible de désactiver le dernier administrateur'], 400);
        }
    }
    
    $stmt = $db->prepare("UPDATE utilisateurs SET actif = 0 WHERE id = ?");
    $stmt->execute([$id]);
    
    logAction('moderation_desactivation_utilisateur', "Utilisateur #$id désactivé: {$utilisateur['email']}");
    
    jsonResponse([
        'success' => true,
        'message' => 'Compte utilisateur désactivé avec succès'
    ]);
}
