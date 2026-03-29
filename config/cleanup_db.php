<?php
/**
 * Script de nettoyage de la base de données
 * Supprime les doublons et nettoie les données
 */

require_once __DIR__ . '/database.php';

function cleanupDatabase() {
    $db = Database::getInstance()->getConnection();
    $results = [];

    // 1. Supprimer les doublons d'annonces (garder la plus ancienne)
    $db->exec("
        DELETE FROM annonces 
        WHERE id NOT IN (
            SELECT MIN(id) 
            FROM annonces 
            GROUP BY utilisateur_id, titre, description
        )
    ");
    $results['annonces_doublons_supprimes'] = $db->rowCount();

    // 2. Supprimer les doublons d'utilisateurs (garder le plus ancien)
    $db->exec("
        DELETE FROM utilisateurs 
        WHERE id NOT IN (
            SELECT MIN(id) 
            FROM utilisateurs 
            GROUP BY email
        )
    ");
    $results['utilisateurs_doublons_supprimes'] = $db->rowCount();

    // 3. Supprimer les doublons de categories (garder la plus ancienne)
    $db->exec("
        DELETE FROM categories 
        WHERE id NOT IN (
            SELECT MIN(id) 
            FROM categories 
            GROUP BY nom
        )
    ");
    $results['categories_doublons_supprimes'] = $db->rowCount();

    // 4. Nettoyer les annonces orphelines (utilisateur supprime)
    $db->exec("
        DELETE FROM annonces 
        WHERE utilisateur_id NOT IN (SELECT id FROM utilisateurs)
    ");
    $results['annonces_orphelines_supprimes'] = $db->rowCount();

    // 5. Nettoyer les annonces avec categorie invalide
    $db->exec("
        UPDATE annonces 
        SET categorie_id = NULL 
        WHERE categorie_id NOT IN (SELECT id FROM categories)
    ");
    $results['annonces_categorie_corrigees'] = $db->rowCount();

    // 6. Compter les elements restants
    $results['total_utilisateurs'] = $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    $results['total_annonces'] = $db->query("SELECT COUNT(*) FROM annonces")->fetchColumn();
    $results['total_categories'] = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $results['total_historique'] = $db->query("SELECT COUNT(*) FROM historique")->fetchColumn();

    // 7. Ajouter l'action dans l'historique
    $stmt = $db->prepare("INSERT INTO historique (utilisateur_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([null, 'nettoyage_base', json_encode($results, JSON_UNESCAPED_UNICODE)]);

    return [
        'success' => true,
        'message' => 'Base de données nettoyée avec succès',
        'results' => $results
    ];
}

// Exécuter le nettoyage si appelé directement
if (basename($_SERVER['PHP_SELF']) === 'cleanup_db.php') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(cleanupDatabase(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
