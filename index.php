<?php
$pageTitle = 'Accueil - Petites Annonces';
require_once 'includes/header.php';

$db = Database::getInstance()->getConnection();

// Recuperer les statistiques
$statsAnnonces = $db->query("SELECT COUNT(*) as total FROM annonces WHERE statut = 'active'")->fetch()['total'];
$statsUsers = $db->query("SELECT COUNT(*) as total FROM utilisateurs WHERE actif = 1")->fetch()['total'];
$statsCategories = $db->query("SELECT COUNT(*) as total FROM categories")->fetch()['total'];

// Recuperer les dernieres annonces
$stmt = $db->query("
    SELECT a.*, c.nom as categorie_nom, u.nom as auteur_nom 
    FROM annonces a 
    LEFT JOIN categories c ON a.categorie_id = c.id 
    LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id 
    WHERE a.statut = 'active' 
    ORDER BY a.date_publication DESC 
    LIMIT 8
");
$annonces = $stmt->fetchAll();

// Recuperer les categories
$categories = $db->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
?>

<!-- Hero Section -->
<section style="text-align: center; padding: 60px 0; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); margin: -40px -20px 40px; padding-left: 20px; padding-right: 20px;">
    <h1 style="font-size: 2.5rem; color: #1e3a5f; margin-bottom: 16px; font-weight: 700;">
        Bienvenue sur Petites Annonces
    </h1>
    <p style="font-size: 1.25rem; color: #64748b; max-width: 600px; margin: 0 auto 32px;">
        Le site pour publier et consulter des petites annonces facilement. Simple, rapide et gratuit.
    </p>
    <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
        <a href="annonces.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 14px 28px;">
            Voir les annonces
        </a>
        <?php if (!isLoggedIn()): ?>
        <a href="register.php" class="btn btn-secondary" style="font-size: 1.1rem; padding: 14px 28px;">
            Creer un compte
        </a>
        <?php else: ?>
        <a href="post.php" class="btn btn-secondary" style="font-size: 1.1rem; padding: 14px 28px;">
            Publier une annonce
        </a>
        <?php endif; ?>
    </div>
</section>

<!-- Statistiques -->
<section style="margin-bottom: 60px;">
    <div class="grid grid-3">
        <div class="stat-card">
            <div class="stat-value"><?= $statsAnnonces ?></div>
            <div class="stat-label">Annonces actives</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $statsUsers ?></div>
            <div class="stat-label">Utilisateurs inscrits</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $statsCategories ?></div>
            <div class="stat-label">Categories</div>
        </div>
    </div>
</section>

<!-- Categories -->
<section style="margin-bottom: 60px;">
    <h2 class="section-title">Parcourir par categorie</h2>
    <div class="grid grid-4">
        <?php foreach ($categories as $cat): ?>
        <a href="annonces.php?categorie=<?= $cat['id'] ?>" class="card" style="text-decoration: none; text-align: center; padding: 24px;">
            <div style="font-size: 2rem; margin-bottom: 12px;">
                <?php
                $icons = [
                    'Immobilier' => '&#127968;',
                    'Vehicules' => '&#128663;',
                    'Electronique' => '&#128241;',
                    'Maison & Jardin' => '&#127793;',
                    'Vetements' => '&#128085;',
                    'Services' => '&#128295;',
                    'Emploi' => '&#128188;',
                    'Loisirs' => '&#127918;'
                ];
                echo $icons[$cat['nom']] ?? '&#128230;';
                ?>
            </div>
            <h3 style="color: #1e3a5f; font-size: 1rem; margin-bottom: 4px;"><?= htmlspecialchars($cat['nom']) ?></h3>
            <p style="color: #6b7280; font-size: 0.85rem;"><?= htmlspecialchars($cat['description'] ?? '') ?></p>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Dernieres annonces -->
<section>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 class="section-title" style="margin-bottom: 0;">Dernieres annonces</h2>
        <a href="annonces.php" class="btn btn-secondary">Voir toutes</a>
    </div>
    
    <?php if (empty($annonces)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">&#128237;</div>
        <h3>Aucune annonce pour le moment</h3>
        <p>Soyez le premier a publier une annonce !</p>
        <?php if (isLoggedIn()): ?>
        <a href="post.php" class="btn btn-primary" style="margin-top: 16px;">Publier une annonce</a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="grid grid-4">
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
                    <a href="annonce.php?id=<?= $annonce['id'] ?>" class="btn btn-sm btn-primary">Voir</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
