<?php
// ==============================================
// TRAITEMENT AVANT TOUT AFFICHAGE HTML
// ==============================================
require_once 'config/database.php';
require_once 'config/helpers.php';

// Verifier que l'utilisateur est connecte
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$userId = getCurrentUserId();
$user = getCurrentUser();

// Traitement de la mise a jour du profil
$updateSuccess = false;
$updateErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newNom = trim($_POST['nom'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($newNom)) {
        $updateErrors[] = 'Le nom est requis.';
    }
    
    if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $updateErrors[] = 'Email invalide.';
    }
    
    $checkEmail = $db->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
    $checkEmail->execute([$newEmail, $userId]);
    if ($checkEmail->fetch()) {
        $updateErrors[] = 'Cet email est deja utilise.';
    }
    
    if ($newPassword && strlen($newPassword) < 6) {
        $updateErrors[] = 'Le mot de passe doit contenir au moins 6 caracteres.';
    }
    
    if ($newPassword && $newPassword !== $confirmPassword) {
        $updateErrors[] = 'Les mots de passe ne correspondent pas.';
    }
    
    if (empty($updateErrors)) {
        if ($newPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE utilisateurs SET nom = ?, email = ?, mot_de_passe = ? WHERE id = ?");
            $stmt->execute([$newNom, $newEmail, $hashedPassword, $userId]);
        } else {
            $stmt = $db->prepare("UPDATE utilisateurs SET nom = ?, email = ? WHERE id = ?");
            $stmt->execute([$newNom, $newEmail, $userId]);
        }
        
        $_SESSION['nom'] = $newNom;
        $_SESSION['email'] = $newEmail;
        $user = getCurrentUser();
        
        logAction('mise_a_jour_profil', 'Profil mis a jour');
        
        // Rediriger apres mise a jour pour eviter re-soumission
        header('Location: account.php?updated=1');
        exit;
    }
}

// Recuperer les statistiques
$statsAnnonces = $db->prepare("SELECT COUNT(*) as total FROM annonces WHERE utilisateur_id = ?");
$statsAnnonces->execute([$userId]);
$totalAnnonces = $statsAnnonces->fetch()['total'];

$statsActives = $db->prepare("SELECT COUNT(*) as total FROM annonces WHERE utilisateur_id = ? AND statut = 'active'");
$statsActives->execute([$userId]);
$annoncesActives = $statsActives->fetch()['total'];

// Recuperer les annonces de l'utilisateur
$stmt = $db->prepare("
    SELECT a.*, c.nom as categorie_nom 
    FROM annonces a 
    LEFT JOIN categories c ON a.categorie_id = c.id 
    WHERE a.utilisateur_id = ? 
    ORDER BY a.date_publication DESC
");
$stmt->execute([$userId]);
$annonces = $stmt->fetchAll();

// Recuperer l'historique des actions
$stmt = $db->prepare("
    SELECT * FROM historique 
    WHERE utilisateur_id = ? 
    ORDER BY date_action DESC 
    LIMIT 10
");
$stmt->execute([$userId]);
$historique = $stmt->fetchAll();

// ==============================================
// AFFICHAGE HTML
// ==============================================
$pageTitle = 'Mon compte - Petites Annonces';
require_once 'includes/header.php';
?>

<div class="grid grid-2" style="gap: 40px;">
    <!-- Colonne gauche: Profil -->
    <div>
        <h1 class="section-title">Mon compte</h1>
        
        <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Profil mis a jour avec succes !</div>
        <?php endif; ?>
        
        <?php if (!empty($updateErrors)): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($updateErrors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Carte profil -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-body" style="padding: 32px;">
                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px;">
                    <div style="width: 80px; height: 80px; background: #1e3a5f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 600;">
                        <?= strtoupper(substr($user['nom'], 0, 1)) ?>
                    </div>
                    <div>
                        <h2 style="font-size: 1.5rem; color: #1e3a5f; margin-bottom: 4px;"><?= htmlspecialchars($user['nom']) ?></h2>
                        <p style="color: #6b7280;"><?= htmlspecialchars($user['email']) ?></p>
                        <?php if (isAdmin()): ?>
                        <span class="badge badge-warning" style="margin-top: 8px;">Administrateur</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label class="form-label" for="nom">Nom complet</label>
                        <input type="text" id="nom" name="nom" class="form-control" 
                               value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Adresse email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" 
                               placeholder="Laisser vide pour ne pas changer">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Confirmer le nouveau mot de passe">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Mettre a jour le profil
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="grid grid-2" style="gap: 16px;">
            <div class="stat-card">
                <div class="stat-value"><?= $totalAnnonces ?></div>
                <div class="stat-label">Annonces publiees</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $annoncesActives ?></div>
                <div class="stat-label">Annonces actives</div>
            </div>
        </div>
    </div>
    
    <!-- Colonne droite: Annonces -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 class="section-title" style="margin-bottom: 0;">Mes annonces</h2>
            <a href="post.php" class="btn btn-primary">Nouvelle annonce</a>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Annonce supprimee avec succes.</div>
        <?php endif; ?>
        
        <?php if (empty($annonces)): ?>
        <div class="empty-state" style="padding: 40px;">
            <div class="empty-state-icon">&#128230;</div>
            <h3>Aucune annonce</h3>
            <p>Vous n'avez pas encore publie d'annonce.</p>
            <a href="post.php" class="btn btn-primary" style="margin-top: 16px;">Publier ma premiere annonce</a>
        </div>
        <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($annonces as $annonce): ?>
            <div class="card">
                <div class="card-body" style="display: flex; gap: 16px; align-items: center;">
                    <div style="width: 80px; height: 80px; background: #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <?php if ($annonce['image']): ?>
                            <img src="uploads/<?= htmlspecialchars($annonce['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                        <?php else: ?>
                            <span style="font-size: 2rem; color: #9ca3af;">&#128247;</span>
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 4px;">
                            <h3 style="font-size: 1rem; color: #1e3a5f; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= htmlspecialchars($annonce['titre']) ?>
                            </h3>
                            <span class="badge <?= $annonce['statut'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                <?= $annonce['statut'] ?>
                            </span>
                        </div>
                        <p style="font-size: 0.9rem; color: #6b7280; margin: 0;">
                            <?= htmlspecialchars($annonce['categorie_nom'] ?? 'Sans categorie') ?> - 
                            <span class="price" style="font-size: 0.9rem;"><?= number_format($annonce['prix'], 2, ',', ' ') ?> EUR</span>
                        </p>
                        <p style="font-size: 0.8rem; color: #9ca3af; margin: 4px 0 0;">
                            Publiee le <?= date('d/m/Y', strtotime($annonce['date_publication'])) ?>
                        </p>
                    </div>
                    <div style="display: flex; gap: 8px; flex-shrink: 0;">
                        <a href="annonce.php?id=<?= $annonce['id'] ?>" class="btn btn-sm btn-secondary">Voir</a>
                        <a href="post.php?edit=<?= $annonce['id'] ?>" class="btn btn-sm btn-primary">Modifier</a>
                        <a href="delete_annonce.php?id=<?= $annonce['id'] ?>&redirect=account.php?deleted=1" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette annonce ?')">Suppr.</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Historique -->
        <?php if (!empty($historique)): ?>
        <h3 style="margin-top: 40px; margin-bottom: 16px; color: #374151;">Historique recent</h3>
        <div class="card">
            <div class="card-body" style="padding: 0;">
                <table class="table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historique as $action): ?>
                        <tr>
                            <td>
                                <span class="badge <?php
                                    if (strpos($action['action'], 'suppression') !== false) echo 'badge-danger';
                                    elseif (strpos($action['action'], 'modification') !== false || strpos($action['action'], 'mise_a_jour') !== false) echo 'badge-warning';
                                    elseif (strpos($action['action'], 'creation') !== false) echo 'badge-success';
                                    else echo 'badge-primary';
                                ?>">
                                    <?= htmlspecialchars(str_replace('_', ' ', $action['action'])) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.85rem; color: #6b7280;"><?= htmlspecialchars($action['details'] ?? '') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($action['date_action'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
