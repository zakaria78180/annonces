<?php
/**
 * Script d'initialisation de la base de données
 * Crée les tables et insère des données de test
 */

require_once __DIR__ . '/database.php';

function initDatabase() {
    $db = Database::getInstance()->getConnection();

    // Créer la table Utilisateurs
    $db->exec("
        CREATE TABLE IF NOT EXISTS utilisateurs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'utilisateur' CHECK(role IN ('utilisateur', 'admin')),
            actif INTEGER DEFAULT 1,
            date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Créer la table Catégories
    $db->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom VARCHAR(100) NOT NULL UNIQUE,
            description TEXT
        )
    ");

    // Créer la table Annonces
    $db->exec("
        CREATE TABLE IF NOT EXISTS annonces (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            titre VARCHAR(200) NOT NULL,
            description TEXT NOT NULL,
            categorie_id INTEGER,
            prix DECIMAL(10,2),
            image VARCHAR(255),
            statut VARCHAR(20) DEFAULT 'active' CHECK(statut IN ('active', 'inactive', 'supprimee')),
            date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
        )
    ");

    // Créer la table Historique (pour les logs)
    $db->exec("
        CREATE TABLE IF NOT EXISTS historique (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER,
            action VARCHAR(100) NOT NULL,
            details TEXT,
            date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        )
    ");

    // Créer les index pour améliorer les performances
    $db->exec("CREATE INDEX IF NOT EXISTS idx_annonces_utilisateur ON annonces(utilisateur_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_annonces_categorie ON annonces(categorie_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_annonces_statut ON annonces(statut)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_utilisateurs_email ON utilisateurs(email)");

    // Insérer les catégories par défaut
    $categories = [
        ['Immobilier', 'Annonces immobilières: vente, location'],
        ['Véhicules', 'Voitures, motos, vélos et autres véhicules'],
        ['Électronique', 'Téléphones, ordinateurs, TV et gadgets'],
        ['Maison & Jardin', 'Meubles, décoration, jardinage'],
        ['Vêtements', 'Mode homme, femme et enfant'],
        ['Services', 'Prestations de services divers'],
        ['Emploi', 'Offres et recherches d\'emploi'],
        ['Loisirs', 'Sports, musique, livres, jeux']
    ];

    $stmt = $db->prepare("INSERT OR IGNORE INTO categories (nom, description) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }

    // Créer un utilisateur admin par défaut
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT OR IGNORE INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Administrateur', 'admin@annonces.com', $adminPassword, 'admin']);

    // Créer un utilisateur test
    $userPassword = password_hash('user123', PASSWORD_DEFAULT);
    $stmt->execute(['Jean Dupont', 'user@test.com', $userPassword, 'utilisateur']);

    // Insérer quelques annonces de test (seulement si la table est vide)
    $countAnnonces = $db->query("SELECT COUNT(*) as cnt FROM annonces")->fetch();
    if ($countAnnonces['cnt'] == 0) {
        $annoncesTest = [
            [2, 'Appartement T3 centre-ville', 'Bel appartement de 65m² avec balcon, proche commerces et transports.', 1, 850.00],
            [2, 'iPhone 14 Pro', 'iPhone 14 Pro 256Go, excellent état, avec boîte et accessoires.', 3, 750.00],
            [2, 'Vélo VTT Rockrider', 'VTT Rockrider 520, taille M, peu utilisé, idéal pour débutant.', 2, 200.00],
        ];

        $stmt = $db->prepare("INSERT INTO annonces (utilisateur_id, titre, description, categorie_id, prix) VALUES (?, ?, ?, ?, ?)");
        foreach ($annoncesTest as $annonce) {
            $stmt->execute($annonce);
        }
    }

    return [
        'success' => true,
        'message' => 'Base de données initialisée avec succès',
        'info' => [
            'admin_email' => 'admin@annonces.com',
            'admin_password' => 'admin123',
            'user_email' => 'user@test.com',
            'user_password' => 'user123'
        ]
    ];
}

// Exécuter l'initialisation si appelé directement
if (basename($_SERVER['PHP_SELF']) === 'init_db.php') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(initDatabase(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
