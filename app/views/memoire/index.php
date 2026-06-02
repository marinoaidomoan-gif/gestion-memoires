<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mémoires</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; }

        header {
            background: #1a1a2e;
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header a { color: #aaa; text-decoration: none; font-size: .9rem; }
        header a:hover { color: #fff; }

        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .toolbar input {
            padding: .6rem 1rem;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            font-size: .9rem;
            width: 280px;
        }

        .btn {
            padding: .6rem 1.2rem;
            background: #4361ee;
            color: #fff;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: .9rem;
            cursor: pointer;
        }
        .btn:hover { background: #3451d1; }

        table {
            width: 100%;
            background: #fff;
            border-radius: 10px;
            border-collapse: collapse;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
        }
        th {
            background: #1a1a2e;
            color: #fff;
            padding: .9rem 1rem;
            text-align: left;
            font-size: .85rem;
        }
        td { padding: .85rem 1rem; border-bottom: 1px solid #f0f0f0; font-size: .9rem; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f9f9ff; }

        .badge {
            padding: .3rem .7rem;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }
        .badge-attente  { background: #fff3cd; color: #856404; }
        .badge-valide   { background: #d1e7dd; color: #0a3622; }
        .badge-rejete   { background: #f8d7da; color: #842029; }

        .actions { display: flex; gap: .5rem; }
        .btn-sm { padding: .3rem .7rem; font-size: .8rem; border-radius: 4px; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #198754; }

        .empty { text-align: center; padding: 3rem; color: #888; }
    </style>
</head>
<body>

<header>
    <strong>📄 Gestion des Mémoires</strong>
    <div>
        Bonjour, <?= htmlspecialchars($user['name']) ?>
        (<?= htmlspecialchars($user['role']) ?>)
        &nbsp;|&nbsp;
        <a href="index.php?url=auth/logout">Déconnexion</a>
    </div>
</header>

<div class="container">

    <div class="toolbar">
        <form method="GET" action="index.php" style="display:flex;gap:.5rem">
            <input type="hidden" name="url" value="memoire">
            <input type="text" name="q" placeholder="Rechercher un mémoire..."
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit" class="btn">Rechercher</button>
        </form>

        <?php if ($user['role'] === 'etudiant'): ?>
            <a href="index.php?url=memoire/soumettre" class="btn">+ Soumettre un mémoire</a>
        <?php endif; ?>
    </div>

    <?php if (empty($memoires)): ?>
        <div class="empty">Aucun mémoire trouvé.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Titre</th>
                <th>Thème</th>
                <?php if ($user['role'] !== 'etudiant'): ?>
                    <th>Étudiant</th>
                    <th>Filière</th>
                <?php endif; ?>
                <th>Date</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($memoires as $m): ?>
            <tr>
                <td><?= $m['idMemoire'] ?></td>
                <td><?= htmlspecialchars($m['titre']) ?></td>
                <td><?= htmlspecialchars($m['theme']) ?></td>
                <?php if ($user['role'] !== 'etudiant'): ?>
                    <td><?= htmlspecialchars($m['nom_etudiant'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($m['filiere'] ?? '-') ?></td>
                <?php endif; ?>
                <td><?= htmlspecialchars($m['date_soumission']) ?></td>
                <td>
                    <?php
                    $badges = [
                        'en_attente' => 'badge-attente',
                        'valide'     => 'badge-valide',
                        'rejete'     => 'badge-rejete',
                    ];
                    $label = [
                        'en_attente' => 'En attente',
                        'valide'     => 'Validé',
                        'rejete'     => 'Rejeté',
                    ];
                    $s = $m['statut'];
                    ?>
                    <span class="badge <?= $badges[$s] ?? '' ?>">
                        <?= $label[$s] ?? $s ?>
                    </span>
                </td>
                <td>
                    <div class="actions">
                        <a href="index.php?url=memoire/afficher/<?= $m['idMemoire'] ?>"
                           class="btn btn-sm">Voir</a>

                        <?php if ($user['role'] === 'directeur' && $m['statut'] === 'en_attente'): ?>
                            <form method="POST" action="index.php?url=memoire/valider/<?= $m['idMemoire'] ?>">
                                <button class="btn btn-sm btn-success">Valider</button>
                            </form>
                            <form method="POST" action="index.php?url=memoire/rejeter/<?= $m['idMemoire'] ?>">
                                <button class="btn btn-sm btn-danger">Rejeter</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</div>
</body>
</html>