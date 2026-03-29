<?php
/**
 * API Authentification
 * Endpoints: inscription, connexion, déconnexion, profil
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'inscription':
        handleInscription();
        break;
    case 'connexion':
        handleConnexion();
        break;
    case 'deconnexion':
        handleDeconnexion();
        break;
    case 'profil':
        handleProfil($method);
        break;
    case 'check':
        handleCheck();
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Action non reconnue'], 400);
}

/**
 * Inscription d'un nouvel utilisateur
 */
function handleInscription() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
    }

    $data = getJsonInput();
    
    // Validation des champs requis
    if (empty($data['nom']) || empty($data['email']) || empty($data['mot_de_passe'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Tous les champs sont requis (nom, email, mot_de_passe)'
        ], 400);
    }

    // Validation de l'email
    if (!validateEmail($data['email'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Adresse email invalide'
        ], 400);
    }

    // Validation du mot de passe (minimum 6 caractères)
    if (strlen($data['mot_de_passe']) < 6) {
        jsonResponse([
            'success' => false,
            'message' => 'Le mot de passe doit contenir au moins 6 caractères'
        ], 400);
    }

    $db = Database::getInstance()->getConnection();

    // Vérifier si l'email existe déjà
    $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([strtolower($data['email'])]);
    
    if ($stmt->fetch()) {
        jsonResponse([
            'success' => false,
            'message' => 'Cette adresse email est déjà utilisée'
        ], 409);
    }

    // Hasher le mot de passe
    $passwordHash = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

    // Insérer le nouvel utilisateur
    $stmt = $db->prepare("
        INSERT INTO utilisateurs (nom, email, mot_de_passe, role) 
        VALUES (?, ?, ?, 'utilisateur')
    ");
    
    try {
        $stmt->execute([
            sanitize($data['nom']),
            strtolower($data['email']),
            $passwordHash
        ]);
        
        $userId = $db->lastInsertId();
        
        logAction('inscription', "Nouvel utilisateur inscrit: {$data['email']}");
        
        jsonResponse([
            'success' => true,
            'message' => 'Inscription réussie',
            'data' => [
                'id' => $userId,
                'nom' => $data['nom'],
                'email' => strtolower($data['email'])
            ]
        ], 201);
        
    } catch (PDOException $e) {
        jsonResponse([
            'success' => false,
            'message' => 'Erreur lors de l\'inscription'
        ], 500);
    }
}

/**
 * Connexion d'un utilisateur
 */
function handleConnexion() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
    }

    $data = getJsonInput();
    
    if (empty($data['email']) || empty($data['mot_de_passe'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Email et mot de passe requis'
        ], 400);
    }

    $db = Database::getInstance()->getConnection();

    // Récupérer l'utilisateur
    $stmt = $db->prepare("
        SELECT id, nom, email, mot_de_passe, role, actif 
        FROM utilisateurs 
        WHERE email = ?
    ");
    $stmt->execute([strtolower($data['email'])]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ], 401);
    }

    // Vérifier si le compte est actif
    if (!$user['actif']) {
        jsonResponse([
            'success' => false,
            'message' => 'Votre compte a été désactivé'
        ], 403);
    }

    // Vérifier le mot de passe
    if (!password_verify($data['mot_de_passe'], $user['mot_de_passe'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ], 401);
    }

    // Créer la session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nom'] = $user['nom'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    logAction('connexion', "Connexion de l'utilisateur: {$user['email']}");

    jsonResponse([
        'success' => true,
        'message' => 'Connexion réussie',
        'data' => [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
}

/**
 * Déconnexion
 */
function handleDeconnexion() {
    if (isLoggedIn()) {
        logAction('deconnexion', "Déconnexion de l'utilisateur");
    }
    
    session_destroy();
    
    jsonResponse([
        'success' => true,
        'message' => 'Déconnexion réussie'
    ]);
}

/**
 * Gestion du profil (GET/PUT)
 */
function handleProfil($method) {
    requireAuth();
    
    $db = Database::getInstance()->getConnection();
    $userId = getCurrentUserId();

    if ($method === 'GET') {
        // Récupérer le profil
        $stmt = $db->prepare("
            SELECT id, nom, email, role, date_inscription 
            FROM utilisateurs 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Compter les annonces de l'utilisateur
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM annonces WHERE utilisateur_id = ? AND statut = 'active'");
        $stmt->execute([$userId]);
        $annonceCount = $stmt->fetch()['count'];

        jsonResponse([
            'success' => true,
            'data' => [
                'utilisateur' => $user,
                'nombre_annonces' => $annonceCount
            ]
        ]);
        
    } elseif ($method === 'PUT') {
        // Modifier le profil
        $data = getJsonInput();
        
        $updates = [];
        $params = [];

        if (!empty($data['nom'])) {
            $updates[] = "nom = ?";
            $params[] = sanitize($data['nom']);
        }

        if (!empty($data['email'])) {
            if (!validateEmail($data['email'])) {
                jsonResponse(['success' => false, 'message' => 'Email invalide'], 400);
            }
            // Vérifier si l'email est déjà utilisé
            $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
            $stmt->execute([strtolower($data['email']), $userId]);
            if ($stmt->fetch()) {
                jsonResponse(['success' => false, 'message' => 'Email déjà utilisé'], 409);
            }
            $updates[] = "email = ?";
            $params[] = strtolower($data['email']);
        }

        if (!empty($data['mot_de_passe'])) {
            if (strlen($data['mot_de_passe']) < 6) {
                jsonResponse(['success' => false, 'message' => 'Mot de passe trop court'], 400);
            }
            $updates[] = "mot_de_passe = ?";
            $params[] = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        }

        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => 'Aucune donnée à modifier'], 400);
        }

        $params[] = $userId;
        $sql = "UPDATE utilisateurs SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        // Mettre à jour la session
        if (!empty($data['nom'])) $_SESSION['nom'] = sanitize($data['nom']);
        if (!empty($data['email'])) $_SESSION['email'] = strtolower($data['email']);

        logAction('modification_profil', 'Profil mis à jour');

        jsonResponse([
            'success' => true,
            'message' => 'Profil mis à jour avec succès'
        ]);
    }
}

/**
 * Vérifier l'état de connexion
 */
function handleCheck() {
    if (isLoggedIn()) {
        jsonResponse([
            'success' => true,
            'connected' => true,
            'data' => getCurrentUser()
        ]);
    } else {
        jsonResponse([
            'success' => true,
            'connected' => false
        ]);
    }
}
