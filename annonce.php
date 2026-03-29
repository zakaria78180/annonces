<?php
require_once 'includes/header.php';

$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: annonces.php');
    exit;
}

// Recuperer l'annonce
$stmt = $db->prepare("
    SELECT a.*, c.nom as categorie_nom, u.nom as auteur_nom, u.email as auteur_email, u.date_inscription as auteur_date
    FROM annonces a 
    LEFT JOIN categories c ON a.categorie_id = c.id 
    LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id 
    WHERE a.id = ? AND a.statut = 'active'
");
$stmt->execute([$id]);
$annonce = $stmt->fetch();

if (!$annonce) {
    header('Location: annonces.php');
    exit;
}

$pageTitle = htmlspecialchars($annonce['titre']) . ' - Petites Annonces';

// Annonces similaires
$stmt = $db->prepare("
    SELECT a.*, c.nom as categorie_nom 
    FROM annonces a 
    LEFT JOIN categories c ON a.categorie_id = c.id 
    WHERE a.categorie_id = ? AND a.id != ? AND a.statut = 'active'
    ORDER BY a.date_publication DESC 
    LIMIT 4
");
$stmt->execute([$annonce['categorie_id'], $id]);
$similaires = $stmt->fetchAll();
?>

<div style="margin-bottom: 24px;">
    <a href="annonces.php" style="color: #6b7280; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        &#8592; Retour aux annonces
    </a>
</div>

<div class="grid grid-2" style="gap: 40px;">
    <!-- Image -->
    <div>
        <div class="card" style="overflow: hidden;">
            <div style="height: 400px; background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%); display: flex; align-items: center; justify-content: center;">
                <?php if ($annonce['image']): ?>
                    <img src="uploads/<?= htmlspecialchars($annonce['image']) ?>" alt="<?= htmlspecialchars($annonce['titre']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <span style="font-size: 6rem; color: #9ca3af;">&#128247;</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Details -->
    <div>
        <span class="badge badge-primary" style="margin-bottom: 16px;"><?= htmlspecialchars($annonce['categorie_nom'] ?? 'Sans categorie') ?></span>
        
        <h1 style="font-size: 2rem; color: #1e3a5f; margin-bottom: 16px;"><?= htmlspecialchars($annonce['titre']) ?></h1>
        
        <p class="price" style="font-size: 2rem; margin-bottom: 24px;"><?= number_format($annonce['prix'], 2, ',', ' ') ?> EUR</p>
        
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-body">
                <h3 style="font-size: 1rem; color: #374151; margin-bottom: 12px;">Description</h3>
                <p style="color: #6b7280; white-space: pre-line;"><?= htmlspecialchars($annonce['description']) ?></p>
            </div>
        </div>
        
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-body">
                <h3 style="font-size: 1rem; color: #374151; margin-bottom: 12px;">Vendeur</h3>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 50px; height: 50px; background: #1e3a5f; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 600;">
                        <?= strtoupper(substr($annonce['auteur_nom'], 0, 1)) ?>
                    </div>
                    <div>
                        <p style="font-weight: 600; color: #1e3a5f;"><?= htmlspecialchars($annonce['auteur_nom']) ?></p>
                        <p style="font-size: 0.85rem; color: #6b7280;">Membre depuis <?= date('F Y', strtotime($annonce['auteur_date'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="mailto:<?= htmlspecialchars($annonce['auteur_email']) ?>?subject=<?= urlencode('Concernant: ' . $annonce['titre']) ?>" class="btn btn-primary" style="flex: 1;">
                Contacter le vendeur
            </a>
            <?php if (isLoggedIn() && (getCurrentUserId() == $annonce['utilisateur_id'] || isAdmin())): ?>
            <a href="post.php?edit=<?= $annonce['id'] ?>" class="btn btn-secondary">Modifier</a>
            <a href="delete_annonce.php?id=<?= $annonce['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette annonce ?')">Supprimer</a>
            <?php endif; ?>
        </div>
        
        <p style="font-size: 0.85rem; color: #9ca3af; margin-top: 24px;">
            Publiee le <?= date('d/m/Y a H:i', strtotime($annonce['date_publication'])) ?>
        </p>
    </div>
</div>

<!-- Annonces similaires -->
<?php if (!empty($similaires)): ?>
<section style="margin-top: 60px;">
    <h2 class="section-title">Annonces similaires</h2>
    <div class="grid grid-4">
        <?php foreach ($similaires as $sim): ?>
        <div class="card annonce-card">
            <div class="annonce-image">
                <?php if ($sim['image']): ?>
                    <img src="uploads/<?= htmlspecialchars($sim['image']) ?>" alt="<?= htmlspecialchars($sim['titre']) ?>">
                <?php else: ?>
                    &#128247;
                <?php endif; ?>
            </div>
            <div class="annonce-content">
                <span class="badge badge-primary annonce-category"><?= htmlspecialchars($sim['categorie_nom'] ?? 'Sans categorie') ?></span>
                <h3 class="annonce-title"><?= htmlspecialchars($sim['titre']) ?></h3>
                <div class="annonce-footer">
                    <span class="price"><?= number_format($sim['prix'], 2, ',', ' ') ?> EUR</span>
                    <a href="annonce.php?id=<?= $sim['id'] ?>" class="btn btn-sm btn-primary">Voir</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
