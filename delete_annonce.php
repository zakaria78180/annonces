<?php
require_once 'config/helpers.php';
require_once 'config/database.php';

// Verifier que l'utilisateur est connecte
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: annonces.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Recuperer l'annonce
$stmt = $db->prepare("SELECT * FROM annonces WHERE id = ?");
$stmt->execute([$id]);
$annonce = $stmt->fetch();

if (!$annonce) {
    header('Location: annonces.php');
    exit;
}

// Verifier les droits (proprietaire ou admin)
if ($annonce['utilisateur_id'] != getCurrentUserId() && !isAdmin()) {
    header('Location: annonces.php');
    exit;
}

// Supprimer l'image si elle existe
if ($annonce['image']) {
    $imagePath = __DIR__ . '/uploads/' . $annonce['image'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Sauvegarder les details de l'annonce avant suppression pour l'historique
$annonceDetails = json_encode([
    'id' => $annonce['id'],
    'titre' => $annonce['titre'],
    'prix' => $annonce['prix'],
    'date_publication' => $annonce['date_publication']
], JSON_UNESCAPED_UNICODE);

// Supprimer l'annonce
$stmt = $db->prepare("DELETE FROM annonces WHERE id = ?");
$result = $stmt->execute([$id]);

// Logger l'action avec details complets
if ($result) {
    logAction('suppression_annonce', "Annonce #{$annonce['id']} '{$annonce['titre']}' supprimee - Details: $annonceDetails");
}

// Rediriger
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'annonces.php?deleted=1';
header("Location: $redirect");
exit;
