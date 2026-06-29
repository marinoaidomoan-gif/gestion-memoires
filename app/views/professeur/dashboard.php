<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <h1><i class="fa-solid fa-gauge-high"></i> Tableau de bord</h1>
    <p>Bienvenue, <strong><?= htmlspecialchars($profil['name'] ?? '') ?></strong>
        — <?= htmlspecialchars($profil['grade'] ?? '') ?>
    </p>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-book"></i></div>
        <div class="stat-info">
            <p>Mémoires encadrés</p>
            <h3><?= $stats['total'] ?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(243,156,18,0.12); color: var(--warning);">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-info">
            <p>En attente</p>
            <h3><?= $stats['en_attente'] ?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(46,204,113,0.12); color: var(--success);">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-info">
            <p>Validés</p>
            <h3><?= $stats['valide'] ?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(231,76,60,0.12); color: var(--danger);">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <div class="stat-info">
            <p>Rejetés</p>
            <h3><?= $stats['rejete'] ?></h3>
        </div>
    </div>
</div>

<!-- Mémoires à évaluer -->
<?php $enAttente = array_filter($mesMemoires, function($m) { return $m['statut'] === 'en_attente'; }); ?>
<?php if (!empty($enAttente)): ?>
    <div class="section-header">
        <h2><i class="fa-solid fa-clock"></i> Mémoires à évaluer</h2>
        <span class="badge badge-en_attente"><?= count($enAttente) ?> en attente</span>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Étudiant</th>
                    <th>Année</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enAttente as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['titre']) ?></td>
                        <td><?= htmlspecialchars($m['nom_etudiant'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($m['annee_academique'] ?? '-') ?></td>
                        <td class="actions-cell">
                            <a href="/gestion_memoires/public/index.php?route=professeur/memoire&id=<?= $m['idMemoire'] ?>"
                               class="btn btn-small btn-primary">
                                <i class="fa-solid fa-eye"></i> Évaluer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Tous les mémoires encadrés -->
<div class="section-header mt-3">
    <h2><i class="fa-solid fa-list"></i> Tous mes mémoires</h2>
</div>

<?php if (empty($mesMemoires)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-book-open"></i>
        <p>Aucun mémoire encadré pour le moment.</p>
    </div>
<?php else: ?>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Étudiant</th>
                    <th>Année</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mesMemoires as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['titre']) ?></td>
                        <td><?= htmlspecialchars($m['nom_etudiant'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($m['annee_academique'] ?? '-') ?></td>
                        <td><span class="badge badge-<?= $m['statut'] ?>"><?= ucfirst($m['statut']) ?></span></td>
                        <td class="actions-cell">
                            <a href="/gestion_memoires/public/index.php?route=professeur/memoire&id=<?= $m['idMemoire'] ?>"
                               class="btn btn-small btn-secondary">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>