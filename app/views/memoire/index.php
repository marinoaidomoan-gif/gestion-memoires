<?php
// app/views/memoire/index.php
// Route: GET /memoire
// Listing tous les mémoires avec filtres et pagination
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="memoire-header">
        <h1>Mémoires</h1>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'etudiant_diplome'): ?>
            <a href="/memoire/soumettre" class="btn btn-primary">+ Soumettre un mémoire</a>
        <?php endif; ?>
    </div>

    <!-- Filtres -->
    <div class="filters">
        <form method="GET" action="/memoire">
            <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="brouillon">Brouillon</option>
                <option value="soumis">Soumis</option>
                <option value="approuve">Approuvé</option>
                <option value="rejete">Rejeté</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
        </form>
    </div>

    <!-- Tableau/Grid mémoires -->
    <div class="memoires-grid">
        <?php if (!empty($memoires)): ?>
            <?php foreach ($memoires as $memoire): ?>
                <div class="memoire-card">
                    <div class="card-header">
                        <h3><?= htmlspecialchars($memoire['titre']) ?></h3>
                        <span class="badge badge-<?= $memoire['statut'] ?>"><?= ucfirst($memoire['statut']) ?></span>
                    </div>
                    <p class="card-description"><?= htmlspecialchars(substr($memoire['description'], 0, 150)) ?>...</p>
                    <div class="card-footer">
                        <small>Par <strong><?= htmlspecialchars($memoire['auteur_nom']) ?></strong> • <?= date('d/m/Y', strtotime($memoire['date_creation'])) ?></small>
                        <a href="/memoire/detail/<?= $memoire['id'] ?>" class="btn btn-small">Voir</a>
                        
                        <?php if ($userId == $memoire['user_id'] && $userRole === 'etudiant_diplome'): ?>
                            <a href="/memoire/modifier/<?= $memoire['id'] ?>" class="btn btn-small btn-outline">Modifier</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-results">Aucun mémoire trouvé.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/memoire?page=<?= $i ?>" class="page-link <?= ($currentPage == $i) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>