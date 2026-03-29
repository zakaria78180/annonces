<?php
$pageTitle = 'Toutes les annonces - Petites Annonces';
require_once 'includes/header.php';

$db = Database::getInstance()->getConnection();

// Parametres de recherche
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$categorieId = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$prixMin = isset($_GET['prix_min']) ? (float)$_GET['prix_min'] : 0;
$prixMax = isset($_GET['prix_max']) ? (float)$_GET['prix_max'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Construire la requete
$where = ["a.statut = 'active'"];
$params = [];

if ($search) {
    $where[] = "(a.titre LIKE ? OR a.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categorieId > 0) {
    $where[] = "a.categorie_id = ?";
    $params[] = $categorieId;
}

if ($prixMin > 0) {
    $where[] = "a.prix >= ?";
    $params[] = $prixMin;
}

if ($prixMax > 0) {
    $where[] = "a.prix <= ?";
    $params[] = $prixMax;
}

$whereClause = implode(' AND ', $where);

// Compter le total
$countStmt = $db->prepare("SELECT COUNT(*) as total FROM annonces a WHERE $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetch()['total'];
$totalPages = ceil($total / $limit);

// Recuperer les annonces
$sql = "
    SELECT a.*, c.nom as categorie_nom, u.nom as auteur_nom 
    FROM annonces a 
    LEFT JOIN categories c ON a.categorie_id = c.id 
    LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id 
    WHERE $whereClause 
    ORDER BY a.date_publication DESC 
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$annonces = $stmt->fetchAll();

// Recuperer les categories pour le filtre
$categories = $db->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

// Message de suppression
$deleteSuccess = isset($_GET['deleted']) && $_GET['deleted'] == 1;
?>

<h1 class="section-title">Toutes les annonces</h1>
<p class="section-subtitle"><?= $total ?> annonce<?= $total > 1 ? 's' : '' ?> trouvee<?= $total > 1 ? 's' : '' ?></p>

<?php if ($deleteSuccess): ?>
<div class="alert alert-success">Annonce supprimee avec succes.</div>
<?php endif; ?>

<!-- Barre de recherche -->
<form method="GET" class="search-bar">
    <input type="text" name="q" class="form-control" placeholder="Rechercher une annonce..." value="<?= htmlspecialchars($search) ?>">
    <select name="categorie" class="form-control">
        <option value="">Toutes les categories</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= $categorieId == $cat['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['nom']) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="prix_min" class="form-control" placeholder="Prix min" value="<?= $prixMin ?: '' ?>" style="max-width: 120px;">
    <input type="number" name="prix_max" class="form-control" placeholder="Prix max" value="<?= $prixMax ?: '' ?>" style="max-width: 120px;">
    <button type="submit" class="btn btn-primary">Rechercher</button>
    <?php if ($search || $categorieId || $prixMin || $prixMax): ?>
    <a href="annonces.php" class="btn btn-secondary">Effacer</a>
    <?php endif; ?>
</form>

<?php if (empty($annonces)): ?>
<div class="empty-state">
    <div class="empty-state-icon">&#128269;</div>
    <h3>Aucune annonce trouvee</h3>
    <p>Essayez de modifier vos criteres de recherche.</p>
</div>
<?php else: ?>
<div class="grid grid-3">
    <?php foreach ($annonces as $annonce): ?>
    <div class="card annonce-card">
        <div class="annonce-image">
            <?php if ($annonce['image']): ?>
                <img src="uploads/<?= htmlspecialchars($annonce['image']) ?>" alt="<?= htmlspecialchars($annonce['titre']) ?>">
            <?php else: ?>
                &#128247;
            <?php endif; ?>
        </div>
        <div class="annonce-content">
            <span class="badge badge-primary annonce-category"><?= htmlspecialchars($annonce['categorie_nom'] ?? 'Sans categorie') ?></span>
            <h3 class="annonce-title"><?= htmlspecialchars($annonce['titre']) ?></h3>
            <p class="annonce-description"><?= htmlspecialchars($annonce['description']) ?></p>
            <div class="annonce-footer">
                <span class="price"><?= number_format($annonce['prix'], 2, ',', ' ') ?> EUR</span>
                <div class="annonce-actions">
                    <a href="annonce.php?id=<?= $annonce['id'] ?>" class="btn btn-sm btn-primary">Voir</a>
                    <?php if (isLoggedIn() && (getCurrentUserId() == $annonce['utilisateur_id'] || isAdmin())): ?>
                    <a href="post.php?edit=<?= $annonce['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                    <a href="delete_annonce.php?id=<?= $annonce['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette annonce ?')">Suppr.</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div style="display: flex; justify-content: center; gap: 8px; margin-top: 40px;">
    <?php if ($page > 1): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-secondary">Precedent</a>
    <?php endif; ?>
    
    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
       class="btn <?= $i == $page ? 'btn-primary' : 'btn-secondary' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
    
    <?php if ($page < $totalPages): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-secondary">Suivant</a>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
