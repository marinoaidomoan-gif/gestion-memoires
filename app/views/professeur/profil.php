<?php
// app/views/professeur/profil.php
?>

<?php require_once __DIR__ . '/dashboard.php'; ?>

<div class="pro-modal-overlay">
    <div class="pro-modal-card">

        <a href="/gestion_memoires/public/index.php?route=professeur/dashboard" class="pro-modal-close" title="Fermer">
            <i class="fa-solid fa-xmark"></i>
        </a>

        <!-- En-tête -->
        <div class="pro-profile-header">
            <div class="pro-avatar-wrapper">
                <div class="pro-avatar" style="border-color: rgba(168,85,247,0.35); color: #c084fc; box-shadow: 0 0 20px rgba(168,85,247,0.12);">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <span class="pro-status-badge"></span>
            </div>
            <h2 class="pro-profile-name">
                <?= htmlspecialchars(($profil['prenom'] ?? '') . ' ' . ($profil['name'] ?? 'Professeur')) ?>
            </h2>
            <p class="pro-profile-role">
                <?= htmlspecialchars($profil['grade'] ?? 'Professeur') ?>
            </p>
        </div>

        <!-- Informations -->
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
                    <div class="pro-info-icon"><i class="fa-solid fa-flask"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Spécialité</span>
                        <span class="pro-info-value"><?= htmlspecialchars($profil['specialite'] ?? 'Non renseigné') ?></span>
                    </div>
                </div>

                <div class="pro-info-item">
                    <div class="pro-info-icon"><i class="fa-solid fa-award"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Grade</span>
                        <span class="pro-info-value" style="color:#c084fc; font-weight:600;">
                            <?= htmlspecialchars($profil['grade'] ?? 'Non renseigné') ?>
                        </span>
                    </div>
                </div>

                <div class="pro-info-item">
                    <div class="pro-info-icon"><i class="fa-solid fa-sitemap"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Département</span>
                        <span class="pro-info-value"><?= htmlspecialchars($profil['departement'] ?? 'Non renseigné') ?></span>
                    </div>
                </div>

                <div class="pro-info-item">
                    <div class="pro-info-icon"><i class="fa-solid fa-building-columns"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Institution</span>
                        <span class="pro-info-value">UATM / GASA Formation</span>
                    </div>
                </div>

            </div>
        </div>

        <div class="pro-profile-footer">
            <a href="/gestion_memoires/public/index.php?route=professeur/dashboard" class="pro-btn-dismiss">
                <i class="fa-solid fa-arrow-left"></i> Revenir au tableau de bord
            </a>
        </div>

    </div>
</div>