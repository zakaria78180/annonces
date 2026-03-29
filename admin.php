<?php
// ==============================================
// TRAITEMENT AVANT TOUT AFFICHAGE HTML
// ==============================================
require_once 'config/database.php';
require_once 'config/helpers.php';

// Verifier que l'utilisateur est admin
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Traitement des actions POST (redirections possibles)
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    
    switch ($action) {
        case 'toggle_user':
            $stmtUser = $db->prepare("SELECT nom FROM utilisateurs WHERE id = ?");
            $stmtUser->execute([$id]);
            $targetUser = $stmtUser->fetch();
            
            $stmt = $db->prepare("UPDATE utilisateurs SET actif = NOT actif WHERE id = ? AND role != 'admin'");
            $stmt->execute([$id]);
            logAction('moderation_utilisateur', "Statut utilisateur #$id '{$targetUser['nom']}' modifie");
            $message = "Statut de l'utilisateur '{$targetUser['nom']}' mis a jour.";
            break;
            
        case 'delete_user':
            $stmtUser = $db->prepare("SELECT nom FROM utilisateurs WHERE id = ?");
            $stmtUser->execute([$id]);
            $targetUser = $stmtUser->fetch();
            
            // Supprimer aussi les annonces de l'utilisateur
            $db->prepare("DELETE FROM annonces WHERE utilisateur_id = ?")->execute([$id]);
            $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = ? AND role != 'admin'");
            $stmt->execute([$id]);
            logAction('suppression_utilisateur', "Utilisateur #$id '{$targetUser['nom']}' supprime avec ses annonces");
            $message = "Utilisateur '{$targetUser['nom']}' supprime.";
            break;
            
        case 'toggle_annonce':
            $stmtAnn = $db->prepare("SELECT titre, statut FROM annonces WHERE id = ?");
            $stmtAnn->execute([$id]);
            $targetAnn = $stmtAnn->fetch();
            $newStatut = $targetAnn['statut'] === 'active' ? 'inactive' : 'active';
            
            $stmt = $db->prepare("UPDATE annonces SET statut = ? WHERE id = ?");
            $stmt->execute([$newStatut, $id]);
            logAction('moderation_annonce', "Annonce #$id '{$targetAnn['titre']}' changee en '$newStatut'");
            $message = "Annonce '{$targetAnn['titre']}' est maintenant $newStatut.";
            break;
            
        case 'delete_annonce':
            $stmtAnn = $db->prepare("SELECT titre FROM annonces WHERE id = ?");
            $stmtAnn->execute([$id]);
            $targetAnn = $stmtAnn->fetch();
            
            $stmt = $db->prepare("DELETE FROM annonces WHERE id = ?");
            $stmt->execute([$id]);
            logAction('suppression_annonce', "Annonce #$id '{$targetAnn['titre']}' supprimee par admin");
            $message = "Annonce '{$targetAnn['titre']}' supprimee.";
            break;
    }
    
    // Rediriger pour eviter re-soumission du formulaire
    header("Location: admin.php?msg=" . urlencode($message) . "&type=success");
    exit;
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'] ?? 'success';
}

// Statistiques generales
$stats = [
    'utilisateurs' => $db->query("SELECT COUNT(*) as total FROM utilisateurs")->fetch()['total'],
    'utilisateurs_actifs' => $db->query("SELECT COUNT(*) as total FROM utilisateurs WHERE actif = 1")->fetch()['total'],
    'annonces' => $db->query("SELECT COUNT(*) as total FROM annonces")->fetch()['total'],
    'annonces_actives' => $db->query("SELECT COUNT(*) as total FROM annonces WHERE statut = 'active'")->fetch()['total'],
    'categories' => $db->query("SELECT COUNT(*) as total FROM categories")->fetch()['total'],
];

// Statistiques par categorie
$statsCat = $db->query("
    SELECT c.nom, COUNT(a.id) as total 
    FROM categories c 
    LEFT JOIN annonces a ON c.id = a.categorie_id AND a.statut = 'active'
    GROUP BY c.id 
    ORDER BY total DESC
")->fetchAll();

// Utilisateurs les plus actifs
$topUsers = $db->query("
    SELECT u.nom, u.email, COUNT(a.id) as total_annonces
    FROM utilisateurs u
    LEFT JOIN annonces a ON u.id = a.utilisateur_id
    GROUP BY u.id
    ORDER BY total_annonces DESC
    LIMIT 5
")->fetchAll();

// Tous les utilisateurs
$utilisateurs = $db->query("SELECT * FROM utilisateurs ORDER BY date_inscription DESC")->fetchAll();

// Toutes les annonces
$annonces = $db->query("
    SELECT a.*, c.nom as categorie_nom, u.nom as auteur_nom 
    FROM annonces a 
    LEFT JOIN categories c ON a.categorie_id = c.id 
    LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id 
    ORDER BY a.date_publication DESC
")->fetchAll();

// Historique global (dernières 20 actions)
$historiqueGlobal = $db->query("
    SELECT h.*, u.nom as user_nom 
    FROM historique h 
    LEFT JOIN utilisateurs u ON h.utilisateur_id = u.id 
    ORDER BY h.date_action DESC 
    LIMIT 20
")->fetchAll();

// ==============================================
// AFFICHAGE HTML
// ==============================================
$pageTitle = 'Administration - Petites Annonces';
require_once 'includes/header.php';
?>

<h1 class="section-title">Administration</h1>

<?php if ($message): ?>
<div class="alert alert-<?= htmlspecialchars($messageType ?: 'success') ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Statistiques -->
<div class="grid grid-4" style="margin-bottom: 40px;">
    <div class="stat-card">
        <div class="stat-value"><?= $stats['utilisateurs'] ?></div>
        <div class="stat-label">Utilisateurs (<?= $stats['utilisateurs_actifs'] ?> actifs)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['annonces'] ?></div>
        <div class="stat-label">Annonces (<?= $stats['annonces_actives'] ?> actives)</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['categories'] ?></div>
        <div class="stat-label">Categories</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['annonces'] > 0 ? round(($stats['annonces_actives'] / $stats['annonces']) * 100) : 0 ?>%</div>
        <div class="stat-label">Taux d'activation</div>
    </div>
</div>

<!-- Onglets -->
<div class="tabs" id="adminTabs">
    <button class="tab active" onclick="showTab('users', this)">Utilisateurs</button>
    <button class="tab" onclick="showTab('annonces', this)">Annonces</button>
    <button class="tab" onclick="showTab('stats', this)">Statistiques</button>
    <button class="tab" onclick="showTab('historique', this)">Historique</button>
</div>

<!-- Section Utilisateurs -->
<div id="tab-users" class="tab-content">
    <div class="card">
        <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Statut</th>
                        <th>Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['nom']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="badge <?= $u['role'] === 'admin' ? 'badge-warning' : 'badge-primary' ?>">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $u['actif'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $u['actif'] ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></td>
                        <td>
                            <?php if ($u['role'] !== 'admin'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_user">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-sm <?= $u['actif'] ? 'btn-secondary' : 'btn-success' ?>">
                                    <?= $u['actif'] ? 'Desactiver' : 'Activer' ?>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet utilisateur et toutes ses annonces ?')">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                            <?php else: ?>
                            <span style="color: #9ca3af; font-size: 0.85rem;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Section Annonces -->
<div id="tab-annonces" class="tab-content" style="display: none;">
    <div class="card">
        <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Categorie</th>
                        <th>Auteur</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($annonces as $a): ?>
                    <tr>
                        <td><?= $a['id'] ?></td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <a href="annonce.php?id=<?= $a['id'] ?>" style="color: #1e3a5f; text-decoration: none;">
                                <?= htmlspecialchars($a['titre']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($a['categorie_nom'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($a['auteur_nom']) ?></td>
                        <td><?= number_format($a['prix'], 2, ',', ' ') ?> EUR</td>
                        <td>
                            <span class="badge <?= $a['statut'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                <?= $a['statut'] ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($a['date_publication'])) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_annonce">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" class="btn btn-sm <?= $a['statut'] === 'active' ? 'btn-secondary' : 'btn-success' ?>">
                                    <?= $a['statut'] === 'active' ? 'Desactiver' : 'Activer' ?>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette annonce ?')">
                                <input type="hidden" name="action" value="delete_annonce">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Section Statistiques -->
<div id="tab-stats" class="tab-content" style="display: none;">
    <div class="grid grid-2" style="gap: 24px;">
        <div class="card">
            <div class="card-body">
                <h3 style="font-size: 1.1rem; color: #1e3a5f; margin-bottom: 16px;">Annonces par categorie</h3>
                <?php foreach ($statsCat as $cat): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                    <span><?= htmlspecialchars($cat['nom']) ?></span>
                    <span class="badge badge-primary"><?= $cat['total'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h3 style="font-size: 1.1rem; color: #1e3a5f; margin-bottom: 16px;">Utilisateurs les plus actifs</h3>
                <?php foreach ($topUsers as $index => $u): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="width: 24px; height: 24px; background: <?= $index < 3 ? '#f59e0b' : '#e5e7eb' ?>; color: <?= $index < 3 ? 'white' : '#6b7280' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600;">
                            <?= $index + 1 ?>
                        </span>
                        <div>
                            <p style="font-weight: 500; margin: 0;"><?= htmlspecialchars($u['nom']) ?></p>
                            <p style="font-size: 0.8rem; color: #6b7280; margin: 0;"><?= htmlspecialchars($u['email']) ?></p>
                        </div>
                    </div>
                    <span class="badge badge-success"><?= $u['total_annonces'] ?> annonces</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Section Historique -->
<div id="tab-historique" class="tab-content" style="display: none;">
    <div class="card">
        <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="table" style="margin: 0;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historiqueGlobal)): ?>
                    <tr><td colspan="4" style="text-align: center; color: #9ca3af; padding: 32px;">Aucun historique enregistre.</td></tr>
                    <?php else: ?>
                    <?php foreach ($historiqueGlobal as $h): ?>
                    <tr>
                        <td style="white-space: nowrap;"><?= date('d/m/Y H:i:s', strtotime($h['date_action'])) ?></td>
                        <td><?= htmlspecialchars($h['user_nom'] ?? 'Systeme') ?></td>
                        <td>
                            <span class="badge <?php
                                if (strpos($h['action'], 'suppression') !== false) echo 'badge-danger';
                                elseif (strpos($h['action'], 'modification') !== false || strpos($h['action'], 'moderation') !== false || strpos($h['action'], 'mise_a_jour') !== false) echo 'badge-warning';
                                elseif (strpos($h['action'], 'creation') !== false || strpos($h['action'], 'connexion') !== false) echo 'badge-success';
                                else echo 'badge-primary';
                            ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $h['action'])) ?>
                            </span>
                        </td>
                        <td style="font-size: 0.85rem; color: #6b7280; max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?= htmlspecialchars($h['details'] ?? '') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showTab(tabName, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tabName).style.display = 'block';
    btn.classList.add('active');
}
</script>

<?php require_once 'includes/footer.php'; ?>
