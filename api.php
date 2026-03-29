<?php
$pageTitle = 'Documentation API - Petites Annonces';
require_once 'includes/header.php';
?>

<h1 class="section-title">Documentation API</h1>
<p class="section-subtitle">API REST complete pour integrer les petites annonces dans vos applications</p>

<!-- Collection Postman -->
<div class="card" style="margin-bottom: 24px; border: 2px solid #f97316;">
    <div class="card-body">
        <h2 style="font-size: 1.25rem; color: #1e3a5f; margin-bottom: 16px; display: flex; align-items: center; gap: 12px;">
            <svg width="32" height="32" viewBox="0 0 256 256" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="256" height="256" rx="128" fill="#FF6C37"/>
                <path d="M128 48C84.65 48 49.6 83.05 49.6 126.4C49.6 169.75 84.65 204.8 128 204.8C171.35 204.8 206.4 169.75 206.4 126.4C206.4 83.05 171.35 48 128 48ZM128 185.6C95.2 185.6 68.8 159.2 68.8 126.4C68.8 93.6 95.2 67.2 128 67.2C160.8 67.2 187.2 93.6 187.2 126.4C187.2 159.2 160.8 185.6 128 185.6Z" fill="white"/>
                <path d="M128 86.4C105.6 86.4 88 104 88 126.4C88 148.8 105.6 166.4 128 166.4C150.4 166.4 168 148.8 168 126.4C168 104 150.4 86.4 128 86.4Z" fill="white"/>
            </svg>
            Tester avec Postman
        </h2>
        <p style="color: #6b7280; margin-bottom: 16px;">Une collection Postman complete est disponible pour tester tous les endpoints de l'API.</p>
        
        <div style="background: #fff7ed; border: 1px solid #fed7aa; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
            <h4 style="color: #c2410c; margin-bottom: 12px;">Instructions d'import :</h4>
            <ol style="color: #9a3412; font-size: 0.9rem; margin-left: 20px; line-height: 1.8;">
                <li>Ouvrez Postman et cliquez sur <strong>Import</strong> (en haut a gauche)</li>
                <li>Selectionnez le fichier <code style="background: white; padding: 2px 6px; border-radius: 4px;">postman/Petites_Annonces_API.postman_collection.json</code></li>
                <li>La collection "Petites Annonces - API REST" apparait dans la barre laterale</li>
                <li>Executez d'abord <strong>"1.2 Connexion (Admin)"</strong> pour obtenir une session</li>
                <li>Testez les autres endpoints dans l'ordre</li>
            </ol>
        </div>
        
        <div style="background: #f0fdf4; border: 1px solid #86efac; padding: 16px; border-radius: 8px;">
            <h4 style="color: #166534; margin-bottom: 8px;">Contenu de la collection :</h4>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 12px;">
                <div style="background: white; padding: 12px; border-radius: 6px;">
                    <strong style="color: #1e3a5f;">1. Authentification</strong>
                    <p style="color: #6b7280; font-size: 0.85rem; margin-top: 4px;">7 endpoints (inscription, connexion, profil...)</p>
                </div>
                <div style="background: white; padding: 12px; border-radius: 6px;">
                    <strong style="color: #1e3a5f;">2. Annonces</strong>
                    <p style="color: #6b7280; font-size: 0.85rem; margin-top: 4px;">7 endpoints (CRUD complet + filtres)</p>
                </div>
                <div style="background: white; padding: 12px; border-radius: 6px;">
                    <strong style="color: #1e3a5f;">3. Categories</strong>
                    <p style="color: #6b7280; font-size: 0.85rem; margin-top: 4px;">5 endpoints (CRUD admin)</p>
                </div>
                <div style="background: white; padding: 12px; border-radius: 6px;">
                    <strong style="color: #1e3a5f;">4. Administration</strong>
                    <p style="color: #6b7280; font-size: 0.85rem; margin-top: 4px;">9 endpoints (stats, moderation)</p>
                </div>
                <div style="background: white; padding: 12px; border-radius: 6px; grid-column: span 2;">
                    <strong style="color: #1e3a5f;">5. Tests de validation</strong>
                    <p style="color: #6b7280; font-size: 0.85rem; margin-top: 4px;">5 tests d'erreurs (email invalide, 404, 401, 403...)</p>
                </div>
            </div>
        </div>
        
        <a href="postman/Petites_Annonces_API.postman_collection.json" download 
           style="display: inline-flex; align-items: center; gap: 8px; background: #f97316; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 16px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
            </svg>
            Telecharger la collection Postman
        </a>
    </div>
</div>

<!-- Comptes de test -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <h2 style="font-size: 1.25rem; color: #1e3a5f; margin-bottom: 16px;">Comptes de test</h2>
        <div class="grid grid-2" style="gap: 16px;">
            <div style="background: #f8fafc; padding: 16px; border-radius: 8px;">
                <h4 style="color: #1e3a5f; margin-bottom: 8px;">Administrateur <span class="badge badge-warning">Admin</span></h4>
                <code style="display: block; background: white; padding: 8px; border-radius: 4px; margin-bottom: 4px;">admin@annonces.com</code>
                <code style="display: block; background: white; padding: 8px; border-radius: 4px;">admin123</code>
            </div>
            <div style="background: #f8fafc; padding: 16px; border-radius: 8px;">
                <h4 style="color: #1e3a5f; margin-bottom: 8px;">Utilisateur <span class="badge badge-success">Actif</span></h4>
                <code style="display: block; background: white; padding: 8px; border-radius: 4px; margin-bottom: 4px;">user@test.com</code>
                <code style="display: block; background: white; padding: 8px; border-radius: 4px;">user123</code>
            </div>
        </div>
    </div>
</div>

<!-- API Authentification -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <h2 style="font-size: 1.25rem; color: #1e3a5f; margin-bottom: 16px;">Authentification</h2>
        
        <div style="background: #f8fafc; border-left: 4px solid #10b981; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">POST</span>
                <code style="font-size: 0.9rem;">/api/auth.php?action=inscription</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Creer un nouveau compte</p>
            <pre style="background: #1e293b; color: #e2e8f0; padding: 12px; border-radius: 8px; margin-top: 12px; overflow-x: auto;"><code>{
  "nom": "Jean Dupont",
  "email": "jean@example.com",
  "mot_de_passe": "motdepasse123"
}</code></pre>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #10b981; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">POST</span>
                <code style="font-size: 0.9rem;">/api/auth.php?action=connexion</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Se connecter</p>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">GET</span>
                <code style="font-size: 0.9rem;">/api/auth.php?action=profil</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Obtenir le profil de l'utilisateur connecte</p>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #10b981; padding: 16px; border-radius: 0 8px 8px 0;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">POST</span>
                <code style="font-size: 0.9rem;">/api/auth.php?action=deconnexion</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Se deconnecter</p>
        </div>
    </div>
</div>

<!-- API Annonces -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <h2 style="font-size: 1.25rem; color: #1e3a5f; margin-bottom: 16px;">Annonces</h2>
        
        <div style="background: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">GET</span>
                <code style="font-size: 0.9rem;">/api/annonces.php</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Lister toutes les annonces</p>
            <p style="color: #9ca3af; font-size: 0.8rem; margin-top: 8px;">Parametres: page, limit, categorie, q, prix_min, prix_max, tri, ordre</p>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">GET</span>
                <code style="font-size: 0.9rem;">/api/annonces.php?id={id}</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Details d'une annonce</p>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #10b981; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">POST</span>
                <code style="font-size: 0.9rem;">/api/annonces.php</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Creer une annonce (authentifie)</p>
            <pre style="background: #1e293b; color: #e2e8f0; padding: 12px; border-radius: 8px; margin-top: 12px; overflow-x: auto;"><code>{
  "titre": "Mon annonce",
  "description": "Description...",
  "categorie_id": 1,
  "prix": 99.99
}</code></pre>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">PUT</span>
                <code style="font-size: 0.9rem;">/api/annonces.php?id={id}</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Modifier une annonce (proprietaire ou admin)</p>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #ef4444; padding: 16px; border-radius: 0 8px 8px 0;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">DELETE</span>
                <code style="font-size: 0.9rem;">/api/annonces.php?id={id}</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Supprimer une annonce (proprietaire ou admin)</p>
        </div>
    </div>
</div>

<!-- API Categories -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <h2 style="font-size: 1.25rem; color: #1e3a5f; margin-bottom: 16px;">Categories</h2>
        
        <div style="background: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">GET</span>
                <code style="font-size: 0.9rem;">/api/categories.php</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Lister toutes les categories</p>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #10b981; padding: 16px; border-radius: 0 8px 8px 0;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">POST</span>
                <code style="font-size: 0.9rem;">/api/categories.php</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Creer une categorie (admin)</p>
        </div>
    </div>
</div>

<!-- API Admin -->
<div class="card">
    <div class="card-body">
        <h2 style="font-size: 1.25rem; color: #1e3a5f; margin-bottom: 16px;">Administration <span class="badge badge-warning">Admin requis</span></h2>
        
        <div style="background: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">GET</span>
                <code style="font-size: 0.9rem;">/api/admin/stats.php</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Statistiques generales</p>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">GET</span>
                <code style="font-size: 0.9rem;">/api/admin/stats.php?type=categories</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Statistiques par categorie</p>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 0 8px 8px 0; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">PUT</span>
                <code style="font-size: 0.9rem;">/api/admin/moderation.php?action=activer&id={id}</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Activer une annonce</p>
        </div>
        
        <div style="background: #f8fafc; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 0 8px 8px 0;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 0.85rem;">PUT</span>
                <code style="font-size: 0.9rem;">/api/admin/moderation.php?action=desactiver&id={id}</code>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem;">Desactiver une annonce</p>
        </div>
    </div>
</div>

<!-- Structure BDD -->
<div class="card" style="margin-top: 24px;">
    <div class="card-body">
        <h2 style="font-size: 1.25rem; color: #1e3a5f; margin-bottom: 16px;">Structure de la base de donnees</h2>
        <p style="color: #6b7280; margin-bottom: 16px;">Base SQLite compatible avec DB Browser for SQLite</p>
        
        <div class="grid grid-2" style="gap: 16px;">
            <div style="background: #f8fafc; padding: 16px; border-radius: 8px;">
                <h4 style="color: #1e3a5f; margin-bottom: 12px;">Table: utilisateurs</h4>
                <ul style="color: #6b7280; font-size: 0.9rem; list-style: none;">
                    <li>id (INTEGER, PK)</li>
                    <li>nom (VARCHAR 100)</li>
                    <li>email (VARCHAR 255, UNIQUE)</li>
                    <li>mot_de_passe (VARCHAR 255)</li>
                    <li>role (VARCHAR 20)</li>
                    <li>actif (INTEGER)</li>
                    <li>date_inscription (DATETIME)</li>
                </ul>
            </div>
            <div style="background: #f8fafc; padding: 16px; border-radius: 8px;">
                <h4 style="color: #1e3a5f; margin-bottom: 12px;">Table: annonces</h4>
                <ul style="color: #6b7280; font-size: 0.9rem; list-style: none;">
                    <li>id (INTEGER, PK)</li>
                    <li>utilisateur_id (INTEGER, FK)</li>
                    <li>titre (VARCHAR 200)</li>
                    <li>description (TEXT)</li>
                    <li>categorie_id (INTEGER, FK)</li>
                    <li>prix (DECIMAL)</li>
                    <li>image (VARCHAR 255)</li>
                    <li>statut (VARCHAR 20)</li>
                    <li>date_publication (DATETIME)</li>
                </ul>
            </div>
            <div style="background: #f8fafc; padding: 16px; border-radius: 8px;">
                <h4 style="color: #1e3a5f; margin-bottom: 12px;">Table: categories</h4>
                <ul style="color: #6b7280; font-size: 0.9rem; list-style: none;">
                    <li>id (INTEGER, PK)</li>
                    <li>nom (VARCHAR 100, UNIQUE)</li>
                    <li>description (TEXT)</li>
                </ul>
            </div>
            <div style="background: #f8fafc; padding: 16px; border-radius: 8px;">
                <h4 style="color: #1e3a5f; margin-bottom: 12px;">Table: historique</h4>
                <ul style="color: #6b7280; font-size: 0.9rem; list-style: none;">
                    <li>id (INTEGER, PK)</li>
                    <li>utilisateur_id (INTEGER, FK)</li>
                    <li>action (VARCHAR 100)</li>
                    <li>details (TEXT)</li>
                    <li>date_action (DATETIME)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
