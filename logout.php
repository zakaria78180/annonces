<?php
require_once 'config/helpers.php';

// Logger l'action avant de detruire la session
if (isLoggedIn()) {
    logAction('deconnexion', 'Deconnexion reussie');
}

// Detruire la session
session_destroy();

// Rediriger vers la page d'accueil ou afficher un message
$showMessage = isset($_GET['show']) && $_GET['show'] == '1';

if (!$showMessage) {
    header('Location: logout.php?show=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deconnexion - Petites Annonces</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 48px;
            text-align: center;
            max-width: 400px;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 24px;
        }
        h1 {
            font-size: 1.5rem;
            color: #1e3a5f;
            margin-bottom: 12px;
        }
        p {
            color: #6b7280;
            margin-bottom: 32px;
        }
        .btn {
            display: inline-block;
            padding: 12px 32px;
            background: #1e3a5f;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #2d5a87;
        }
        .links {
            margin-top: 24px;
            font-size: 0.9rem;
        }
        .links a {
            color: #1e3a5f;
            text-decoration: none;
            margin: 0 12px;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">&#128075;</div>
        <h1>Vous etes deconnecte</h1>
        <p>A bientot sur Petites Annonces !</p>
        <a href="index.php" class="btn">Retour a l'accueil</a>
        <div class="links">
            <a href="login.php">Se reconnecter</a>
            <a href="register.php">Creer un compte</a>
        </div>
    </div>
</body>
</html>
