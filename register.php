<?php
// ==============================================
// TRAITEMENT AVANT TOUT AFFICHAGE HTML
// ==============================================
require_once 'config/database.php';
require_once 'config/helpers.php';

// Rediriger si deja connecte
if (isLoggedIn()) {
    header('Location: account.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($nom)) {
        $errors[] = 'Le nom est requis.';
    }
    
    if (empty($email)) {
        $errors[] = 'L\'email est requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'email n\'est pas valide.';
    }
    
    if (empty($password)) {
        $errors[] = 'Le mot de passe est requis.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caracteres.';
    }
    
    if ($password !== $password_confirm) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }
    
    if (empty($errors)) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $errors[] = 'Cet email est deja utilise.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$nom, $email, $hashedPassword])) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $errors[] = 'Erreur lors de l\'inscription. Veuillez reessayer.';
            }
        }
    }
}

// ==============================================
// AFFICHAGE HTML
// ==============================================
$pageTitle = 'Inscription - Petites Annonces';
require_once 'includes/header.php';
?>

<div style="max-width: 450px; margin: 40px auto;">
    <div class="card">
        <div class="card-body" style="padding: 40px;">
            <h1 style="font-size: 1.75rem; color: #1e3a5f; text-align: center; margin-bottom: 8px;">Inscription</h1>
            <p style="text-align: center; color: #6b7280; margin-bottom: 32px;">
                Creez votre compte gratuitement
            </p>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="nom">Nom complet</label>
                    <input type="text" id="nom" name="nom" class="form-control" 
                           placeholder="Jean Dupont" required
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="votre@email.com" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Minimum 6 caracteres" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" 
                           placeholder="Repetez le mot de passe" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                    S'inscrire
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 24px; color: #6b7280;">
                Deja inscrit ? 
                <a href="login.php" style="color: #1e3a5f; font-weight: 500;">Connectez-vous</a>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
