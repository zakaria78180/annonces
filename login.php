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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            logAction('connexion', 'Connexion reussie');
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}

// ==============================================
// AFFICHAGE HTML
// ==============================================
$pageTitle = 'Connexion - Petites Annonces';
require_once 'includes/header.php';
?>

<div style="max-width: 450px; margin: 40px auto;">
    <div class="card">
        <div class="card-body" style="padding: 40px;">
            <h1 style="font-size: 1.75rem; color: #1e3a5f; text-align: center; margin-bottom: 8px;">Connexion</h1>
            <p style="text-align: center; color: #6b7280; margin-bottom: 32px;">
                Connectez-vous a votre compte
            </p>
            
            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Inscription reussie ! Vous pouvez maintenant vous connecter.</div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="votre@email.com" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Votre mot de passe" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                    Se connecter
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 24px; color: #6b7280;">
                Pas encore de compte ? 
                <a href="register.php" style="color: #1e3a5f; font-weight: 500;">Inscrivez-vous</a>
            </p>
            
            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <p style="font-size: 0.85rem; color: #9ca3af; text-align: center; margin-bottom: 12px;">
                    Comptes de test disponibles :
                </p>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                    <code style="background: #f3f4f6; padding: 8px 12px; border-radius: 6px; font-size: 0.8rem;">
                        admin@annonces.com / admin123
                    </code>
                    <code style="background: #f3f4f6; padding: 8px 12px; border-radius: 6px; font-size: 0.8rem;">
                        user@test.com / user123
                    </code>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
