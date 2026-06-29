<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <h1><i class="fa-solid fa-gauge-high"></i> Tableau de bord</h1>
    <p>Bienvenue, <strong><?= htmlspecialchars($profil['name'] ?? '') ?></strong>
        — Directeur des Études
    </p>
</div>

<!-- Stats statuts -->
<div class="stats-grid">
    <?php
    $statsMap = [
        'en_attente' => ['label' => 'En attente',  'icon' => 'fa-clock',        'color' => 'warning'],
        'valide'     => ['label' => 'Validés',      'icon' => 'fa-circle-check', 'color' => 'success'],
        'rejete'     => ['label' => 'Rejetés',      'icon' => 'fa-circle-xmark', 'color' => 'danger'],
    ];
    $totalMemoires = count($tousLesMemoires);
    $totalUsers    = count($tousLesUsers);
    ?>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-book"></i></div>
        <div class="stat-info">
            <p>Total mémoires</p>
            <h3><?= $totalMemoires ?></h3>
        </div>
    </div>
    <?php foreach ($statsStatuts as $stat): ?>
        <?php $map = $statsMap[$stat['statut']] ?? null; ?>
        <?php if ($map): ?>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(var(--<?= $map['color'] ?>-rgb, 243,156,18),0.12); color: var(--<?= $map['color'] ?>);">
                    <i class="fa-solid <?= $map['icon'] ?>"></i>
                </div>
                <div class="stat-info">
                    <p><?= $map['label'] ?></p>
                    <h3><?= $stat['total'] ?? 0 ?></h3>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(52,152,219,0.12); color: var(--info);">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-info">
            <p>Utilisateurs</p>
            <h3><?= $totalUsers ?></h3>
        </div>
    </div>
</div>

<!-- Mémoires en attente -->
<div class="section-header">
    <h2><i class="fa-solid fa-clock"></i> Mémoires en attente de validation</h2>
    <span class="badge badge-en_attente"><?= count($memoiresEnAttente) ?> en attente</span>
</div>

<?php if (empty($memoiresEnAttente)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-circle-check"></i>
        <p>Aucun mémoire en attente.</p>
    </div>
<?php else: ?>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Étudiant</th>
                    <th>Professeur</th>
                    <th>Année</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($memoiresEnAttente as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['titre']) ?></td>
                        <td><?= htmlspecialchars($m['nom_etudiant'] ?? '-') ?></td>
                        <td>
                            <?php if (empty($m['idProfesseur'])): ?>
                                <span class="text-muted">Non assigné</span>
                                <!-- Assigner un professeur -->
                                <form class="inline-form" method="POST"
                                      action="/gestion_memoires/public/index.php?route=memoire/assigner">
                                    <input type="hidden" name="idMemoire" value="<?= $m['idMemoire'] ?>">
                                    <select name="idProfesseur" class="select-sm">
                                        <option value="">-- Assigner --</option>
                                        <?php foreach ($professeurs as $p): ?>
                                            <option value="<?= $p['idUser'] ?>">
                                                <?= htmlspecialchars($p['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-small btn-outline">
                                        <i class="fa-solid fa-user-plus"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <?= htmlspecialchars($m['nom_professeur'] ?? '-') ?>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($m['annee_academique'] ?? '-') ?></td>
                        <td class="actions-cell">
                            <a href="/gestion_memoires/public/index.php?route=memoire/detail&id=<?= $m['idMemoire'] ?>"
                               class="btn btn-small btn-secondary">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <form method="POST"
                                  action="/gestion_memoires/public/index.php?route=memoire/valider"
                                  style="display:inline;">
                                <input type="hidden" name="idMemoire" value="<?= $m['idMemoire'] ?>">
                                <button type="submit" class="btn btn-small btn-success">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>
                            <form method="POST"
                                  action="/gestion_memoires/public/index.php?route=memoire/rejeter"
                                  style="display:inline;">
                                <input type="hidden" name="idMemoire" value="<?= $m['idMemoire'] ?>">
                                <button type="submit" class="btn btn-small btn-danger">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Gestion utilisateurs -->
<div class="section-header mt-3">
    <h2><i class="fa-solid fa-users"></i> Utilisateurs</h2>
    <a href="/gestion_memoires/public/index.php?route=admin/creer-compte" class="btn btn-primary">
        <i class="fa-solid fa-user-plus"></i> Créer un compte
    </a>
</div>

<?php if (!empty($tousLesUsers)): ?>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tousLesUsers as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="badge badge-role">
                                <?= ucfirst(str_replace('_', ' ', $u['role'])) ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <?php if ($u['idUser'] !== $_SESSION['idUser']): ?>
                                <form method="POST"
                                      action="/gestion_memoires/public/index.php?route=admin/supprimer-user"
                                      style="display:inline;"
                                      onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    <input type="hidden" name="idUser" value="<?= $u['idUser'] ?>">
                                    <button type="submit" class="btn btn-small btn-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>