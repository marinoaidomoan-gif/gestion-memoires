<?php
// app/views/etudiant/profil_consulteur.php
?>

<?php require_once __DIR__ . '/dashboard_consulteur.php'; ?>

<div class="pro-modal-overlay">
    <div class="pro-modal-card">

        <a href="/gestion_memoires/public/index.php?route=etudiant/dashboard" class="pro-modal-close" title="Fermer">
            <i class="fa-solid fa-xmark"></i>
        </a>

        <!-- En-tête -->
        <div class="pro-profile-header">
            <div class="pro-avatar-wrapper">
                <div class="pro-avatar" style="border-color: rgba(16,185,129,0.35); color: #34d399; box-shadow: 0 0 20px rgba(16,185,129,0.12);">
                    <i class="fa-solid fa-user"></i>
                </div>
                <span class="pro-status-badge"></span>
            </div>
            <h2 class="pro-profile-name">
                <?= htmlspecialchars(($profil['prenom'] ?? '') . ' ' . ($profil['name'] ?? 'Étudiant')) ?>
            </h2>
            <p class="pro-profile-role">Étudiant Consulteur</p>
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

                <div class="pro-info-item">
                    <div class="pro-info-icon"><i class="fa-solid fa-eye"></i></div>
                    <div class="pro-info-content">
                        <span class="pro-info-label">Accès</span>
                        <span class="pro-info-value" style="color:#34d399; font-weight:600;">
                            Consultation & Commentaires
                        </span>
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
            <a href="/gestion_memoires/public/index.php?route=etudiant/dashboard" class="pro-btn-dismiss">
                <i class="fa-solid fa-arrow-left"></i> Revenir au tableau de bord
            </a>
        </div>

    </div>
</div>