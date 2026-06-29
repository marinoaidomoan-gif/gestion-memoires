<?php
// app/views/layouts/sidebar.php
// Inclus uniquement si l'utilisateur est connecté (géré dans header.php)

$role          = $_SESSION['role']   ?? '';
$nom           = $_SESSION['name']   ?? 'Utilisateur';
$routeActuelle = $_GET['route']      ?? '';

// Helper : classe active
function sidebarActive($route, $current) {
    return strpos($current, $route) === 0 ? 'active' : '';
}
?>

<aside class="sidebar" id="sidebar">

    <!-- En-tête sidebar : info utilisateur -->
    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <i class="fa-solid fa-circle-user"></i>
        </div>
        <div class="sidebar-user-info">
            <p class="sidebar-user-name"><?= htmlspecialchars($nom) ?></p>
            <span class="sidebar-user-role">
                <?php
                    $labels = [
                        'etudiant_diplome'    => 'Etudiant Diplômé',
                        'etudiant_consulteur' => 'Etudiant Consulteur',
                        'professeur'          => 'Professeur',
                        'directeur_etudes'    => 'Directeur des Etudes',
                    ];
                    echo $labels[$role] ?? ucfirst($role);
                ?>
            </span>
        </div>
    </div>

    <div class="sidebar-divider"></div>

    <!-- Navigation principale -->
    <nav class="sidebar-nav">

        <!-- ===== LIENS COMMUNS A TOUS ===== -->
        <p class="sidebar-section-label">Navigation</p>

        <a href="/gestion_memoires/public/index.php?route=memoires"
           class="sidebar-link <?= sidebarActive('memoires', $routeActuelle) ?>">
            <i class="fa-solid fa-layer-group"></i>
            <span>Tous les mémoires</span>
        </a>

        <!-- ===== ETUDIANT DIPLOME ===== -->
        <?php if ($role === 'etudiant_diplome'): ?>

            <a href="/gestion_memoires/public/index.php?route=etudiant/dashboard"
               class="sidebar-link <?= sidebarActive('etudiant/dashboard', $routeActuelle) ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Tableau de bord</span>
            </a>

            <div class="sidebar-divider"></div>
            <p class="sidebar-section-label">Mes mémoires</p>

            <a href="/gestion_memoires/public/index.php?route=memoire/soumettre"
               class="sidebar-link <?= sidebarActive('memoire/soumettre', $routeActuelle) ?>">
                <i class="fa-solid fa-file-arrow-up"></i>
                <span>Soumettre un mémoire</span>
            </a>

            <a href="/gestion_memoires/public/index.php?route=etudiant/mes-memoires"
               class="sidebar-link <?= sidebarActive('etudiant/mes-memoires', $routeActuelle) ?>">
                <i class="fa-solid fa-folder-open"></i>
                <span>Mes soumissions</span>
            </a>

            <a href="/gestion_memoires/public/index.php?route=etudiant/commentaires"
               class="sidebar-link <?= sidebarActive('etudiant/commentaires', $routeActuelle) ?>">
                <i class="fa-solid fa-comments"></i>
                <span>Mes commentaires</span>
            </a>

        <?php endif; ?>

        <!-- ===== ETUDIANT CONSULTEUR ===== -->
        <?php if ($role === 'etudiant_consulteur'): ?>

            <a href="/gestion_memoires/public/index.php?route=etudiant/dashboard"
               class="sidebar-link <?= sidebarActive('etudiant/dashboard', $routeActuelle) ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Tableau de bord</span>
            </a>

            <div class="sidebar-divider"></div>
            <p class="sidebar-section-label">Activité</p>

            <a href="/gestion_memoires/public/index.php?route=etudiant/commentaires"
               class="sidebar-link <?= sidebarActive('etudiant/commentaires', $routeActuelle) ?>">
                <i class="fa-solid fa-comments"></i>
                <span>Mes commentaires</span>
            </a>

            <a href="/gestion_memoires/public/index.php?route=etudiant/likes"
               class="sidebar-link <?= sidebarActive('etudiant/likes', $routeActuelle) ?>">
                <i class="fa-solid fa-heart"></i>
                <span>Mes likes</span>
            </a>

        <?php endif; ?>

        <!-- ===== PROFESSEUR ===== -->
        <?php if ($role === 'professeur'): ?>

            <a href="/gestion_memoires/public/index.php?route=professeur/dashboard"
               class="sidebar-link <?= sidebarActive('professeur/dashboard', $routeActuelle) ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Tableau de bord</span>
            </a>

            <div class="sidebar-divider"></div>
            <p class="sidebar-section-label">Encadrement</p>

            <a href="/gestion_memoires/public/index.php?route=professeur/mes-etudiants"
               class="sidebar-link <?= sidebarActive('professeur/mes-etudiants', $routeActuelle) ?>">
                <i class="fa-solid fa-user-graduate"></i>
                <span>Mes étudiants</span>
            </a>

            <a href="/gestion_memoires/public/index.php?route=professeur/en-attente"
               class="sidebar-link <?= sidebarActive('professeur/en-attente', $routeActuelle) ?>">
                <i class="fa-solid fa-clock"></i>
                <span>Mémoires à évaluer</span>
            </a>

            <a href="/gestion_memoires/public/index.php?route=professeur/evalues"
               class="sidebar-link <?= sidebarActive('professeur/evalues', $routeActuelle) ?>">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>Mémoires évalués</span>
            </a>

            <div class="sidebar-divider"></div>
            <p class="sidebar-section-label">Activité</p>

            <a href="/gestion_memoires/public/index.php?route=professeur/commentaires"
               class="sidebar-link <?= sidebarActive('professeur/commentaires', $routeActuelle) ?>">
                <i class="fa-solid fa-comments"></i>
                <span>Mes commentaires</span>
            </a>

        <?php endif; ?>

        <!-- ===== DIRECTEUR DES ETUDES ===== -->
        <?php if ($role === 'directeur_etudes'): ?>

            <a href="/gestion_memoires/public/index.php?route=admin/dashboard"
               class="sidebar-link <?= sidebarActive('admin/dashboard', $routeActuelle) ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Tableau de bord</span>
            </a>

            <div class="sidebar-divider"></div>
            <p class="sidebar-section-label">Gestion mémoires</p>

            <a href="/gestion_memoires/public/index.php?route=admin/en-attente"
               class="sidebar-link <?= sidebarActive('admin/en-attente', $routeActuelle) ?>">
                <i class="fa-solid fa-clock"></i>
                <span>En attente</span>
            </a>

            <a href="/gestion_memoires/public/index.php?route=admin/valides"
               class="sidebar-link <?= sidebarActive('admin/valides', $routeActuelle) ?>">
                <i class="fa-solid fa-circle-check"></i>
                <span>Validés</span>
            </a>

            <a href="/gestion_memoires/public/index.php?route=admin/rejetes"
               class="sidebar-link <?= sidebarActive('admin/rejetes', $routeActuelle) ?>">
                <i class="fa-solid fa-circle-xmark"></i>
                <span>Rejetés</span>
            </a>

            <a href="/gestion_memoires/public/index.php?route=memoire/upload"
               class="sidebar-link <?= sidebarActive('memoire/upload', $routeActuelle) ?>">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Archiver un mémoire</span>
            </a>

            <div class="sidebar-divider"></div>
            <p class="sidebar-section-label">Administration</p>

            <a href="/gestion_memoires/public/index.php?route=admin/utilisateurs"
               class="sidebar-link <?= sidebarActive('admin/utilisateurs', $routeActuelle) ?>">
                <i class="fa-solid fa-users"></i>
                <span>Utilisateurs</span>
            </a>

            <a href="/gestion_memoires/public/index.php?route=admin/professeurs"
               class="sidebar-link <?= sidebarActive('admin/professeurs', $routeActuelle) ?>">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Professeurs</span>
            </a>

            <a href="/gestion_memoires/public/index.php?route=admin/statistiques"
               class="sidebar-link <?= sidebarActive('admin/statistiques', $routeActuelle) ?>">
                <i class="fa-solid fa-chart-bar"></i>
                <span>Statistiques</span>
            </a>

        <?php endif; ?>

    </nav>

    <div class="sidebar-divider"></div>

    <!-- Bas de sidebar : déconnexion -->
    <div class="sidebar-footer">
        <a href="/gestion_memoires/public/index.php?route=auth/profil" class="sidebar-link">
            <i class="fa-solid fa-user-pen"></i>
            <span>Mon profil</span>
        </a>
        <a href="/gestion_memoires/public/index.php?route=logout" class="sidebar-link sidebar-logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Déconnexion</span>
        </a>
    </div>

    <!-- Bouton réduire sidebar -->
    <button class="sidebar-collapse-btn" id="sidebar-collapse-btn" title="Réduire">
        <i class="fa-solid fa-angles-left" id="sidebar-collapse-icon"></i>
    </button>

</aside>