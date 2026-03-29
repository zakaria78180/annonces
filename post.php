<?php
// ==============================================
// TRAITEMENT AVANT TOUT AFFICHAGE HTML
// (les redirections header() doivent etre avant le HTML)
// ==============================================
require_once 'config/database.php';
require_once 'config/helpers.php';

// Verifier que l'utilisateur est connecte AVANT d'inclure le header
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$categories = $db->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

$errors = [];
$success = false;
$editMode = false;
$annonce = null;
$redirectUrl = null;

// Mode edition
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM annonces WHERE id = ?");
    $stmt->execute([$editId]);
    $annonce = $stmt->fetch();
    
    if ($annonce && ($annonce['utilisateur_id'] == getCurrentUserId() || isAdmin())) {
        $editMode = true;
    } else {
        header('Location: annonces.php');
        exit;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categorieId = (int)($_POST['categorie_id'] ?? 0);
    $prix = (float)($_POST['prix'] ?? 0);
    
    // Validation
    if (empty($titre)) {
        $errors[] = 'Le titre est requis.';
    } elseif (strlen($titre) > 200) {
        $errors[] = 'Le titre ne doit pas depasser 200 caracteres.';
    }
    
    if (empty($description)) {
        $errors[] = 'La description est requise.';
    }
    
    if ($categorieId <= 0) {
        $errors[] = 'Veuillez selectionner une categorie.';
    }
    
    if ($prix < 0) {
        $errors[] = 'Le prix ne peut pas etre negatif.';
    }
    
    // Upload image
    $imageName = $editMode ? $annonce['image'] : null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $result = uploadImage($_FILES['image']);
        if ($result['success']) {
            $imageName = $result['filename'];
        } else {
            $errors[] = $result['message'];
        }
    }
    
    if (empty($errors)) {
        if ($editMode) {
            // Mise a jour
            $stmt = $db->prepare("
                UPDATE annonces 
                SET titre = ?, description = ?, categorie_id = ?, prix = ?, image = ?, date_modification = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$titre, $description, $categorieId, $prix, $imageName, $annonce['id']]);
            logAction('modification_annonce', "Annonce #{$annonce['id']} '{$annonce['titre']}' modifiee - Nouveau titre: '$titre', Nouveau prix: $prix EUR");
            
            // Rediriger apres modification pour eviter re-soumission
            header("Location: annonce.php?id={$annonce['id']}&modified=1");
            exit;
        } else {
            // Creation
            $stmt = $db->prepare("
                INSERT INTO annonces (utilisateur_id, titre, description, categorie_id, prix, image) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([getCurrentUserId(), $titre, $description, $categorieId, $prix, $imageName]);
            $newId = $db->lastInsertId();
            logAction('creation_annonce', "Annonce #$newId '$titre' creee - Prix: $prix EUR");
            header("Location: annonce.php?id=$newId&created=1");
            exit;
        }
    }
}

// ==============================================
// AFFICHAGE HTML (apres tout le traitement)
// ==============================================
$pageTitle = $editMode ? 'Modifier l\'annonce - Petites Annonces' : 'Publier une annonce - Petites Annonces';
require_once 'includes/header.php';
?>

<div style="max-width: 700px; margin: 0 auto;">
    <h1 class="section-title"><?= $editMode ? 'Modifier l\'annonce' : 'Publier une annonce' ?></h1>
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body" style="padding: 32px;">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label" for="titre">Titre de l'annonce *</label>
                    <input type="text" id="titre" name="titre" class="form-control" 
                           placeholder="Ex: iPhone 14 Pro en excellent etat" required maxlength="200"
                           value="<?= htmlspecialchars($_POST['titre'] ?? $annonce['titre'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="description">Description *</label>
                    <textarea id="description" name="description" class="form-control" 
                              placeholder="Decrivez votre article en detail..." required rows="6"><?= htmlspecialchars($_POST['description'] ?? $annonce['description'] ?? '') ?></textarea>
                </div>
                
                <div class="grid grid-2" style="gap: 20px;">
                    <div class="form-group">
                        <label class="form-label" for="categorie_id">Categorie *</label>
                        <select id="categorie_id" name="categorie_id" class="form-control" required>
                            <option value="">Selectionnez une categorie</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($_POST['categorie_id'] ?? $annonce['categorie_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="prix">Prix (EUR)</label>
                        <input type="number" id="prix" name="prix" class="form-control" 
                               placeholder="0.00" step="0.01" min="0"
                               value="<?= htmlspecialchars($_POST['prix'] ?? $annonce['prix'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="image">Image</label>
                    <?php if ($editMode && $annonce['image']): ?>
                    <div style="margin-bottom: 12px;">
                        <img src="uploads/<?= htmlspecialchars($annonce['image']) ?>" alt="Image actuelle" style="max-width: 200px; border-radius: 8px;">
                        <p style="font-size: 0.85rem; color: #6b7280; margin-top: 8px;">Image actuelle. Uploadez une nouvelle image pour la remplacer.</p>
                    </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    <p style="font-size: 0.85rem; color: #6b7280; margin-top: 8px;">Formats acceptes: JPG, PNG, GIF, WebP. Max 5 Mo.</p>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 32px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <?= $editMode ? 'Enregistrer les modifications' : 'Publier l\'annonce' ?>
                    </button>
                    <a href="<?= $editMode ? "annonce.php?id={$annonce['id']}" : 'annonces.php' ?>" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
