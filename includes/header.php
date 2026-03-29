<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/init_db.php';

// Initialiser la base de données UNE SEULE FOIS (vérification via fichier lock)
$lockFile = __DIR__ . '/../database/.db_initialized';
if (!file_exists($lockFile)) {
    initDatabase();
    // Créer le dossier database s'il n'existe pas
    $lockDir = dirname($lockFile);
    if (!file_exists($lockDir)) {
        mkdir($lockDir, 0777, true);
    }
    file_put_contents($lockFile, date('Y-m-d H:i:s'));
}

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Petites Annonces' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            color: white;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: #f59e0b;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        nav a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 6px;
            transition: all 0.2s;
            font-weight: 500;
            font-size: 0.95rem;
        }

        nav a:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        nav a.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-left: 20px;
            padding-left: 20px;
            border-left: 1px solid rgba(255,255,255,0.2);
        }

        .user-info {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
        }

        .user-info strong {
            color: white;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: #f59e0b;
            color: #1e3a5f;
        }

        .btn-primary:hover {
            background: #d97706;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid currentColor;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        /* Main Content */
        main {
            min-height: calc(100vh - 200px);
            padding: 40px 0;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e3a5f;
            margin-bottom: 8px;
        }

        .card-text {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 12px;
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #1e3a5f;
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }

        /* Grid */
        .grid {
            display: grid;
            gap: 24px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        @media (max-width: 992px) {
            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
            .grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            nav {
                flex-wrap: wrap;
                justify-content: center;
            }
            .user-menu {
                margin-left: 0;
                padding-left: 0;
                border-left: none;
                padding-top: 15px;
                border-top: 1px solid rgba(255,255,255,0.2);
            }
            .grid-2, .grid-3, .grid-4 {
                grid-template-columns: 1fr;
            }
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-primary {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Price */
        .price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #059669;
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .table th,
        .table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }

        .table tr:hover {
            background: #f9fafb;
        }

        /* Section */
        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 24px;
        }

        .section-subtitle {
            color: #6b7280;
            font-size: 1rem;
            margin-top: -16px;
            margin-bottom: 24px;
        }

        /* Search */
        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .search-bar .form-control {
            flex: 1;
            min-width: 200px;
        }

        .search-bar select {
            min-width: 180px;
        }

        /* Annonce Card */
        .annonce-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .annonce-image {
            height: 180px;
            background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 3rem;
        }

        .annonce-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .annonce-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .annonce-category {
            margin-bottom: 8px;
        }

        .annonce-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e3a5f;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .annonce-description {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .annonce-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .annonce-actions {
            display: flex;
            gap: 8px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 8px;
            color: #374151;
        }

        /* Stats */
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e3a5f;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 4px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 4px;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 24px;
        }

        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            color: #6b7280;
            position: relative;
            transition: color 0.2s;
        }

        .tab:hover {
            color: #1e3a5f;
        }

        .tab.active {
            color: #1e3a5f;
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: #1e3a5f;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s, visibility 0.2s;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow: auto;
            transform: scale(0.9);
            transition: transform 0.2s;
        }

        .modal-overlay.show .modal {
            transform: scale(1);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e3a5f;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            padding: 4px;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="index.php" class="logo">
                <span class="logo-icon">PA</span>
                <span>Petites Annonces</span>
            </a>
            
            <nav>
                <a href="index.php" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Accueil</a>
                <a href="annonces.php" class="<?= $currentPage === 'annonces' ? 'active' : '' ?>">Annonces</a>
                
                <?php if (isLoggedIn()): ?>
                    <a href="post.php" class="<?= $currentPage === 'post' ? 'active' : '' ?>">Publier</a>
                    <a href="account.php" class="<?= $currentPage === 'account' ? 'active' : '' ?>">Mon compte</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="<?= $currentPage === 'admin' ? 'active' : '' ?>">Administration</a>
                    <?php endif; ?>
                    
                    <div class="user-menu">
                        <span class="user-info">
                            Bonjour, <strong><?= htmlspecialchars($currentUser['nom']) ?></strong>
                            <?php if (isAdmin()): ?>
                                <span class="badge badge-warning">Admin</span>
                            <?php endif; ?>
                        </span>
                        <a href="logout.php" class="btn btn-sm btn-secondary">Deconnexion</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="<?= $currentPage === 'login' ? 'active' : '' ?>">Connexion</a>
                    <a href="register.php" class="btn btn-primary">Inscription</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
