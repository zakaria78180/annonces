<?php
/**
 * Page de nettoyage des doublons
 * Accéder à cette page pour supprimer tous les doublons de la base de données
 */
require_once 'config/database.php';
require_once 'config/helpers.php';
require_once 'config/cleanup_db.php';

$message = '';
$results = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cleanup'])) {
    $result = cleanupDatabase();
    $results = $result['results'];
    
    // Supprimer le fichier lock pour forcer la reinitialisation propre au prochain chargement
    $lockFile = __DIR__ . '/database/.db_initialized';
    if (isset($_POST['reset_lock']) && file_exists($lockFile)) {
        unlink($lockFile);
    }
    
    $message = 'Nettoyage termine avec succes !';
}

// Compter les doublons actuels
$db = Database::getInstance()->getConnection();

$doublonsAnnonces = $db->query("
    SELECT titre, COUNT(*) as nb 
    FROM annonces 
    GROUP BY utilisateur_id, titre, description 
    HAVING COUNT(*) > 1
")->fetchAll();

$doublonsUtilisateurs = $db->query("
    SELECT email, COUNT(*) as nb 
    FROM utilisateurs 
    GROUP BY email 
    HAVING COUNT(*) > 1
")->fetchAll();

$totalAnnonces = $db->query("SELECT COUNT(*) FROM annonces")->fetchColumn();
$totalUtilisateurs = $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$totalCategories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();

$pageTitle = 'Nettoyage de la base de donnees';
require_once 'includes/header.php';
?>

<h1 class="section-title">Nettoyage de la base de donnees</h1>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<!-- Etat actuel -->
<div class="grid grid-3" style="margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-value"><?= $totalAnnonces ?></div>
        <div class="stat-label">Annonces au total</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalUtilisateurs ?></div>
        <div class="stat-label">Utilisateurs au total</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalCategories ?></div>
        <div class="stat-label">Categories au total</div>
    </div>
</div>

<!-- Doublons detectes -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <h2 class="card-title">Doublons detectes</h2>
        
        <?php if (empty($doublonsAnnonces) && empty($doublonsUtilisateurs)): ?>
            <div class="alert alert-success" style="margin-top: 16px;">
                Aucun doublon detecte dans la base de donnees.
            </div>
        <?php else: ?>
            <?php if (!empty($doublonsAnnonces)): ?>
                <h3 style="margin: 16px 0 8px; font-size: 1rem; color: #dc2626;">Annonces en double :</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Nombre de copies</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doublonsAnnonces as $doublon): ?>
                            <tr>
                                <td><?= htmlspecialchars($doublon['titre']) ?></td>
                                <td><span class="badge badge-danger"><?= $doublon['nb'] ?> copies</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($doublonsUtilisateurs)): ?>
                <h3 style="margin: 16px 0 8px; font-size: 1rem; color: #dc2626;">Utilisateurs en double :</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Nombre de copies</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doublonsUtilisateurs as $doublon): ?>
                            <tr>
                                <td><?= htmlspecialchars($doublon['email']) ?></td>
                                <td><span class="badge badge-danger"><?= $doublon['nb'] ?> copies</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($results): ?>
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <h2 class="card-title">Resultat du nettoyage</h2>
        <table class="table" style="margin-top: 16px;">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Resultat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Annonces en double supprimees</td>
                    <td><strong><?= $results['annonces_doublons_supprimes'] ?></strong></td>
                </tr>
                <tr>
                    <td>Utilisateurs en double supprimes</td>
                    <td><strong><?= $results['utilisateurs_doublons_supprimes'] ?></strong></td>
                </tr>
                <tr>
                    <td>Categories en double supprimees</td>
                    <td><strong><?= $results['categories_doublons_supprimes'] ?></strong></td>
                </tr>
                <tr>
                    <td>Annonces orphelines supprimees</td>
                    <td><strong><?= $results['annonces_orphelines_supprimes'] ?></strong></td>
                </tr>
                <tr>
                    <td>Annonces avec categorie corrigees</td>
                    <td><strong><?= $results['annonces_categorie_corrigees'] ?></strong></td>
                </tr>
                <tr style="background: #f0fdf4;">
                    <td><strong>Total annonces restantes</strong></td>
                    <td><strong><?= $results['total_annonces'] ?></strong></td>
                </tr>
                <tr style="background: #f0fdf4;">
                    <td><strong>Total utilisateurs restants</strong></td>
                    <td><strong><?= $results['total_utilisateurs'] ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Bouton de nettoyage -->
<div class="card">
    <div class="card-body" style="text-align: center; padding: 40px;">
        <form method="POST">
            <p style="margin-bottom: 20px; color: #6b7280;">
                Cliquez sur le bouton ci-dessous pour supprimer tous les doublons de la base de donnees.
                <br>Seule la premiere occurrence de chaque element sera conservee.
            </p>
            <input type="hidden" name="cleanup" value="1">
            <label style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 20px; cursor: pointer;">
                <input type="checkbox" name="reset_lock" value="1">
                <span style="font-size: 0.9rem; color: #6b7280;">Reinitialiser aussi le verrou (empeche la re-creation des donnees de test)</span>
            </label>
            <button type="submit" class="btn btn-danger" style="font-size: 1.1rem; padding: 14px 40px;">
                Nettoyer la base de donnees
            </button>
        </form>
    </div>
</div>

<div style="margin-top: 20px; text-align: center;">
    <a href="annonces.php" class="btn btn-secondary">Retour aux annonces</a>
</div>

<?php require_once 'includes/footer.php'; ?>
