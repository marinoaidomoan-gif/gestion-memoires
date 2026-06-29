<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <h1><i class="fa-solid fa-gauge-high"></i> Tableau de bord</h1>
    <p>Bienvenue, <strong><?= htmlspecialchars($profil['name'] ?? '') ?></strong></p>
</div>

<!-- Recherche & Filtres -->
<div class="filters">
    <form method="GET" action="/gestion_memoires/public/index.php">
        <input type="hidden" name="route" value="etudiant/dashboard">
        <input type="text" name="q" placeholder="Rechercher un mémoire..."
               value="<?= htmlspecialchars($filtres['motcle'] ?? '') ?>">
        <input type="text" name="theme" placeholder="Thème..."
               value="<?= htmlspecialchars($filtres['theme'] ?? '') ?>">
        <select name="annee">
            <option value="">Toutes les années</option>
            <?php foreach ($annees as $a): ?>
                <option value="<?= htmlspecialchars($a['annee_academique']) ?>"
                    <?= ($filtres['annee_academique'] ?? '') === $a['annee_academique'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['annee_academique']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i> Rechercher
        </button>
        <?php if (!empty($filtres)): ?>
            <a href="/gestion_memoires/public/index.php?route=etudiant/dashboard" class="btn btn-secondary">
                <i class="fa-solid fa-xmark"></i> Réinitialiser
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Liste mémoires -->
<div class="section-header">
    <h2><i class="fa-solid fa-layer-group"></i> Mémoires disponibles</h2>
    <span class="result-count"><?= count($memoires) ?> résultat(s)</span>
</div>

<?php if (empty($memoires)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-magnifying-glass"></i>
        <p>Aucun mémoire trouvé.</p>
    </div>
<?php else: ?>
    <div class="memoires-grid">
        <?php foreach ($memoires as $m): ?>
            <div class="memoire-card">
                <div class="card-header">
                    <h3><?= htmlspecialchars($m['titre']) ?></h3>
                    <span class="badge badge-<?= $m['statut'] ?>"><?= ucfirst($m['statut']) ?></span>
                </div>
                <p class="card-description">
                    <?= htmlspecialchars(substr($m['theme'] ?? '', 0, 120)) ?>
                </p>
                <div class="card-footer">
                    <small>
                        <i class="fa-solid fa-calendar"></i>
                        <?= htmlspecialchars($m['annee_academique'] ?? '-') ?>
                    </small>
                    <div class="card-actions">
                        <!-- Like bouton -->
                        <button class="like-btn <?= in_array($m['idMemoire'], $memoiresLikes) ? 'liked' : '' ?>"
                                data-memoire-id="<?= $m['idMemoire'] ?>"
                                data-type="memoire">
                            <i class="fa-solid fa-heart"></i>
                            <span class="likes-count"><?= $m['nb_likes'] ?? 0 ?></span>
                        </button>
                        <a href="/gestion_memoires/public/index.php?route=memoire/detail&id=<?= $m['idMemoire'] ?>"
                           class="btn btn-small btn-secondary">
                            <i class="fa-solid fa-eye"></i> Voir
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script src="/gestion_memoires/public/js/like.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>