<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <h1><i class="fa-solid fa-gauge-high"></i> Tableau de bord</h1>
    <p>Bienvenue, <strong><?= htmlspecialchars($profil['name'] ?? '') ?></strong></p>
</div>

<!-- Stats rapides -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-folder-open"></i></div>
        <div class="stat-info">
            <p>Total soumissions</p>
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

<!-- Mes mémoires -->
<div class="section-header">
    <h2><i class="fa-solid fa-book"></i> Mes mémoires</h2>
    <a href="/gestion_memoires/public/index.php?route=memoire/soumettre" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Soumettre
    </a>
</div>

<?php if (empty($mesMemoires)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-folder-open"></i>
        <p>Vous n'avez pas encore soumis de mémoire.</p>
        <a href="/gestion_memoires/public/index.php?route=memoire/soumettre" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Soumettre mon premier mémoire
        </a>
    </div>
<?php else: ?>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Thème</th>
                    <th>Année</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mesMemoires as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['titre']) ?></td>
                        <td><?= htmlspecialchars($m['theme'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($m['annee_academique'] ?? '-') ?></td>
                        <td><span class="badge badge-<?= $m['statut'] ?>"><?= ucfirst($m['statut']) ?></span></td>
                        <td class="actions-cell">
                            <a href="/gestion_memoires/public/index.php?route=memoire/detail&id=<?= $m['idMemoire'] ?>"
                               class="btn btn-small btn-secondary">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <?php if ($m['statut'] === 'en_attente'): ?>
                                <a href="/gestion_memoires/public/index.php?route=memoire/modifier&id=<?= $m['idMemoire'] ?>"
                                   class="btn btn-small btn-outline">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>