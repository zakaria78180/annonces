</div>
    </main>

    <footer style="background: #1e3a5f; color: white; padding: 40px 0; margin-top: 60px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px;">
                <div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 16px;">Petites Annonces</h3>
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem; line-height: 1.6;">
                        Le site pour publier et consulter des petites annonces facilement. 
                        Simple, rapide et gratuit.
                    </p>
                </div>
                <div>
                    <h4 style="font-size: 1rem; margin-bottom: 16px;">Navigation</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 8px;">
                        <li><a href="index.php" style="color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.9rem;">Accueil</a></li>
                        <li><a href="annonces.php" style="color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.9rem;">Annonces</a></li>
                        <li><a href="register.php" style="color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.9rem;">Inscription</a></li>
                        <li><a href="login.php" style="color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.9rem;">Connexion</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="font-size: 1rem; margin-bottom: 16px;">Categories</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 8px;">
                        <?php
                        $db = Database::getInstance()->getConnection();
                        $cats = $db->query("SELECT nom FROM categories LIMIT 4")->fetchAll();
                        foreach ($cats as $cat):
                        ?>
                        <li><a href="annonces.php?categorie=<?= urlencode($cat['nom']) ?>" style="color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.9rem;"><?= htmlspecialchars($cat['nom']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h4 style="font-size: 1rem; margin-bottom: 16px;">Contact</h4>
                    <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">
                        contact@petites-annonces.fr<br>
                        Tel: 01 23 45 67 89
                    </p>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 40px; padding-top: 20px; text-align: center; color: rgba(255,255,255,0.5); font-size: 0.85rem;">
                &copy; <?= date('Y') ?> Petites Annonces - Tous droits reserves | 
                <a href="api.php" style="color: rgba(255,255,255,0.7); text-decoration: none;">Documentation API</a>
            </div>
        </div>
    </footer>

    <script>
        // Fonction pour afficher les messages flash
        function showAlert(message, type = 'success') {
            const alert = document.createElement('div');
            alert.className = 'alert alert-' + type;
            alert.textContent = message;
            alert.style.position = 'fixed';
            alert.style.top = '100px';
            alert.style.right = '20px';
            alert.style.zIndex = '3000';
            alert.style.maxWidth = '400px';
            alert.style.animation = 'slideIn 0.3s ease';
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        // Animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
