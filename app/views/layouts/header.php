<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — GestMémoire' : 'GestMémoire' ?></title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Google Fonts : Syne + DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- CSS Global -->
    <link rel="stylesheet" href="/gestion_memoires/public/css/style.css">

    <?php if (isset($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="/public/css/<?= htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?= isset($_SESSION['role']) ? 'is-authenticated' : 'is-public' ?>">

<?php
    $estConnecte = isset($_SESSION['idUser']);
    $role        = $_SESSION['role'] ?? null;
    $routeActuelle = $_GET['route'] ?? '';
?>

<!-- ===================== NAVBAR PUBLIQUE ===================== -->
<nav class="navbar" id="navbar">
    <div class="navbar-brand">
        <a href="/gestion_memoires/public/index.php?route=memoires">
            <i class="fa-solid fa-book-open"></i>
            <span>GestMémoire</span>
        </a>
    </div>

    <!-- Menu central (public) -->
    <ul class="navbar-menu" id="navbar-menu">
        <li>
            <a href="/gestion_memoires/public/index.php?route=memoires"
               class="<?= strpos($routeActuelle, 'memoires') === 0 ? 'active' : '' ?>">
                <i class="fa-solid fa-layer-group"></i> Mémoires
            </a>
        </li>

        <?php if ($estConnecte): ?>
            <!-- Lien tableau de bord selon rôle -->
            <?php
                $dashRoutes = [
                    'etudiant_diplome'   => 'etudiant/dashboard',
                    'etudiant_consulteur'=> 'etudiant/dashboard',
                    'professeur'         => 'professeur/dashboard',
                    'directeur_etudes'   => 'admin/dashboard',
                ];
                $dashRoute = $dashRoutes[$role] ?? '#';
            ?>
            <li>
                <a href="/gestion_memoires/public/index.php?route=<?= $dashRoute ?>"
                   class="<?= strpos($routeActuelle, 'dashboard') !== false ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i> Tableau de bord
                </a>
            </li>

            <?php if ($role === 'etudiant_diplome'): ?>
                <li>
                    <a href="/gestion_memoires/public/index.php?route=memoire/soumettre"
                       class="<?= $routeActuelle === 'memoire/soumettre' ? 'active' : '' ?>">
                        <i class="fa-solid fa-file-arrow-up"></i> Soumettre
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'directeur_etudes'): ?>
                <li>
                    <a href="/gestion_memoires/public/index.php?route=memoire/upload"
                       class="<?= $routeActuelle === 'memoire/upload' ? 'active' : '' ?>">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Archiver
                    </a>
                </li>
            <?php endif; ?>

        <?php endif; ?>
    </ul>

    <!-- Partie droite : auth -->
    <div class="navbar-auth">
        <?php if ($estConnecte): ?>
            <div class="user-dropdown">
                <button class="user-btn" id="user-dropdown-btn">
                    <i class="fa-solid fa-circle-user"></i>
                    <span><?= htmlspecialchars($_SESSION['name'] ?? 'Utilisateur') ?></span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu" id="user-dropdown-menu">
                    <div class="dropdown-header">
                        <small><?= ucfirst(str_replace('_', ' ', $role ?? '')) ?></small>
                    </div>
                    <a href="/gestion_memoires/public/index.php?route=<?= $dashRoute ?>">
                        <i class="fa-solid fa-gauge-high"></i> Tableau de bord
                    </a>
                    <a href="/gestion_memoires/public/index.php?route=<?php
                            $profilRoutes = [
                                'etudiant_diplome'    => 'etudiant/profil',
                                'etudiant_consulteur' => 'etudiant/profil',
                                'professeur'          => 'professeur/profil',
                                'directeur_etudes'    => 'admin/profil',
                            ];
                            echo $profilRoutes[$role] ?? 'memoires';
                        ?>" class="sidebar-link">
                        <i class="fa-solid fa-user-pen"></i> Mon profil
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="/gestion_memoires/public/index.php?route=logout" class="logout-link">
                        <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                    </a>
                </div>
            </div>
            <?php else: ?>
                <a href="/gestion_memoires/public/index.php?route=login" class="btn btn-outline-nav">
                    <i class="fa-solid fa-right-to-bracket"></i> Connexion
                </a>
                <a href="/gestion_memoires/public/index.php?route=register" class="btn btn-primary-nav">
                    <i class="fa-solid fa-user-plus"></i> Inscription
                </a>
            <?php endif; ?>

        <!-- Hamburger mobile -->
        <button class="navbar-toggle" id="navbar-toggle" aria-label="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</nav>

<!-- ===================== LAYOUT WRAPPER ===================== -->
<?php if ($estConnecte): ?>
    <!-- Page avec sidebar après connexion -->
    <div class="app-layout">

        <!-- SIDEBAR -->
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="main-content" id="main-content">

            <!-- Breadcrumb -->
            <?php if (isset($breadcrumb)): ?>
                <nav class="breadcrumb-nav" aria-label="fil d'ariane">
                    <ol class="breadcrumb">
                        <li><a href="/public/index.php?route=memoires"><i class="fa-solid fa-house"></i></a></li>
                        <?php foreach ($breadcrumb as $label => $url): ?>
                            <?php if ($url): ?>
                                <li><a href="<?= $url ?>"><?= htmlspecialchars($label) ?></a></li>
                            <?php else: ?>
                                <li class="active"><?= htmlspecialchars($label) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>

            <!-- Flash messages -->
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="flash flash-<?= $_SESSION['flash']['type'] ?>">
                    <i class="fa-solid <?= $_SESSION['flash']['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

<?php else: ?>
    <!-- Page publique sans sidebar -->
    <main class="main-public">

        <!-- Flash messages -->
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="flash flash-<?= $_SESSION['flash']['type'] ?>">
                <i class="fa-solid <?= $_SESSION['flash']['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

<?php endif; ?>