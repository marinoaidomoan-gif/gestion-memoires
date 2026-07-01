<?php
// app/views/etudiant/profil_diplome.php
?>

<?php require_once __DIR__ . '/dashboard_diplome.php'; ?>

<div class="pro-modal-overlay">
    <div class="pro-modal-card">

        <a href="/gestion_memoires/public/index.php?route=etudiant/dashboard" class="pro-modal-close" title="Fermer">
            <i class="fa-solid fa-xmark"></i>
        </a>

        <!-- En-tête -->
        <div class="pro-profile-header">
            <div class="pro-avatar-wrapper">
                <div class="pro-avatar" style="border-color: rgba(59,130,246,0.35); color: #60a5fa; box-shadow: 0 0 20px rgba(59,130,246,0.12);">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <span class="pro-status-badge"></span>
            </div>
            <h2 class="pro-profile-name">
                <?= htmlspecialchars(($profil['prenom'] ?? '') . ' ' . ($profil['name'] ?? 'Étudiant')) ?>
            </h2>
            <p class="pro-profile-role">Étudiant Diplômé</p>
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
                    <div class="pro-info-icon"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Niveau</span>
                        <span class="pro-info-value"><?= htmlspecialchars($profil['niveau'] ?? 'Non renseigné') ?></span>
                    </div>
                </div>

                <div class="pro-info-item">
                    <div class="pro-info-icon"><i class="fa-solid fa-code-branch"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Filière</span>
                        <span class="pro-info-value"><?= htmlspecialchars($profil['filiere'] ?? 'Non renseigné') ?></span>
                    </div>
                </div>

                <?php if (!empty($profil['annee_diplome'])): ?>
                <div class="pro-info-item">
                    <div class="pro-info-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Année de diplôme</span>
                        <span class="pro-info-value" style="color:#60a5fa; font-weight:600;">
                            <?= htmlspecialchars($profil['annee_diplome']) ?>
                        </span>
                    </div>
                </div>
                <?php endif; ?>

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
            <a href="/gestion_memoires/public/index.php?route=etudiant/dashboard" class="pro-btn-dismiss">
                <i class="fa-solid fa-arrow-left"></i> Revenir au tableau de bord
            </a>
        </div>

    </div>
</div>