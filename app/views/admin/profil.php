<?php
// app/views/admin/profil.php
// Fenêtre modale de profil haut de gamme pour le Directeur des Études
?>

<!-- Le tableau de bord reste visible et flouté en arrière-plan -->
<?php require_once __DIR__ . '/dashboard.php'; ?>

<div class="pro-modal-overlay">
    <div class="pro-modal-card">
        
        <!-- Bouton Fermer discret en haut à droite -->
        <a href="/gestion_memoires/public/index.php?route=admin/dashboard" class="pro-modal-close" title="Fermer">
            <i class="fa-solid fa-xmark"></i>
        </a>

        <!-- En-tête : Identité visuelle -->
        <div class="pro-profile-header">
            <div class="pro-avatar-wrapper">
                <div class="pro-avatar">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <span class="pro-status-badge"></span>
            </div>
            <h2 class="pro-profile-name">
                <?= htmlspecialchars(($profil['prenom'] ?? '') . ' ' . ($profil['name'] ?? 'Administrateur')) ?>
            </h2>
            <p class="pro-profile-role">Directeur des Études</p>
        </div>

        <!-- Corps : Grille d'informations épurée -->
        <div class="pro-profile-body">
            <div class="pro-info-grid">
                
                <div class="pro-info-item">
                    <div class="pro-info-icon"><i class="fa-solid fa-envelope"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Adresse Email</span>
                        <span class="pro-info-value"><?= htmlspecialchars($profil['email'] ?? 'Non renseigné') ?></span>
                    </div>
                </div>

                <div class="pro-info-item">
                    <div class="pro-info-icon"><i class="fa-solid fa-building-user"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Bureau Assigné</span>
                        <span class="pro-info-value"><?= htmlspecialchars($profil['bureau'] ?? 'Zone Admin / Non spécifié') ?></span>
                    </div>
                </div>

                <div class="pro-info-item">
                    <div class="pro-info-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Niveau d'accès</span>
                        <span class="pro-info-value accent-gold">Contrôle Total (Super-Admin)</span>
                    </div>
                </div>

                <div class="pro-info-item">
                    <div class="pro-info-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Institution</span>
                        <span class="pro-info-value">UATM / GASA Formation</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Pied de page : Bouton d'action minimaliste -->
        <div class="pro-profile-footer">
            <a href="/gestion_memoires/public/index.php?route=admin/dashboard" class="pro-btn-dismiss">
                <i class="fa-solid fa-arrow-left"></i> Revenir au tableau de bord
            </a>
        </div>

    </div>
</div>
