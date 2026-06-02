<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Admin</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; }

        header {
            background: #1a1a2e; color: #fff;
            padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        header a { color: #aaa; text-decoration: none; font-size: .9rem; }
        header nav a { margin-left: 1.2rem; color: #ccc; }
        header nav a:hover { color: #fff; }

        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }

        h2 { color: #1a1a2e; margin-bottom: 1.5rem; }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem; margin-bottom: 2rem;
        }
        .stat-card {
            background: #fff; border-radius: 10px;
            padding: 1.5rem; text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.07);
        }
        .stat-card .number { font-size: 2.2rem; font-weight: 700; color: #4361ee; }
        .stat-card .label  { color: #666; font-size: .88rem; margin-top: .3rem; }
        .stat-card.warning .number { color: #f39c12; }
        .stat-card.success .number { color: #198754; }
        .stat-card.danger  .number { color: #dc3545; }

        .card {
            background: #fff; border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.07);
        }
        h3 { color: #1a1a2e; margin-bottom: 1rem; font-size: 1rem; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: .7rem 1rem; text-align: left; font-size: .83rem; color: #555; }
        td { padding: .75rem 1rem; border-bottom: 1px solid #f0f0f0; font-size: .88rem; }
        tr:last-child td { border: none; }

        .badge { padding: .3rem .7rem; border-radius: 20px; font-size: .75rem; font-weight: 600; }
        .badge-attente { background: #fff3cd; color: #856404; }

        .btn-sm {
            padding: .3rem .8rem; font-size: .8rem; border-radius: 4px;
            border: none; cursor: pointer; text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #4361ee; color: #fff; }
        .btn-success { background: #198754; color: #fff; }
        .btn-danger  { background: #dc3545; color: #fff; }
    </style>
</head>
<body>

<header>
    <strong>🎓 Gestion des Mémoires — Admin</strong>
    <nav>
        <a href="index.php?url=admin/dashboard">Dashboard</a>
        <a href="index.php?url=admin/users">Utilisateurs</a>
        <a href="index.php?url=memoire">Mémoires</a>
        <a href="index.php?url=auth/logout">Déconnexion</a>
    </nav>
</header>

<div class="container">
    <h2>Bonjour, <?= htmlspecialchars($user['name']) ?> 👋</h2>

    <!-- Statistiques -->
    <div class="stats">
        <div class="stat-card">
            <div class="number"><?= $stats['total_memoires'] ?></div>
            <div class="label">Total mémoires</div>
        </div>
        <div class="stat-card warning">
            <div class="number"><?= $stats['en_attente'] ?></div>
            <div class="label">En attente</div>
        </div>
        <div class="stat-card success">
            <div class="number"><?= $stats['valides'] ?></div>
            <div class="label">Validés</div>
        </div>
        <div class="stat-card danger">
            <div class="number"><?= $stats['rejetes'] ?></div>
            <div class="label">Rejetés</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['total_users'] ?></div>
            <div class="label">Utilisateurs</div>
        </div>
    </div>

    <!-- Mémoires en attente -->
    <div class="card">
        <h3>⏳ Mémoires en attente de validation</h3>
        <?php if (empty($memoires_recents)): ?>
            <p style="color:#888;font-size:.9rem">Aucun mémoire en attente.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Titre</th><th>Thème</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($memoires_recents as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['titre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($m['theme'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($m['date_soumission']) ?></td>
                    <td style="display:flex;gap:.4rem">
                        <a href="index.php?url=memoire/afficher/<?= $m['idMemoire'] ?>"
                           class="btn-sm btn-primary">Voir</a>
                        <form method="POST" action="index.php?url=memoire/valider/<?= $m['idMemoire'] ?>">
                            <button class="btn-sm btn-success">Valider</button>
                        </form>
                        <form method="POST" action="index.php?url=memoire/rejeter/<?= $m['idMemoire'] ?>">
                            <button class="btn-sm btn-danger">Rejeter</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>