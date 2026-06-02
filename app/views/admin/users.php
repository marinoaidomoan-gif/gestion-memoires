<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Utilisateurs — Admin</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; }

        header {
            background: #1a1a2e; color: #fff;
            padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        header nav a { margin-left: 1.2rem; color: #ccc; text-decoration: none; font-size: .9rem; }
        header nav a:hover { color: #fff; }

        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1rem;
                     display: grid; grid-template-columns: 1fr 380px; gap: 1.5rem; }

        .card {
            background: #fff; border-radius: 10px;
            padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,.07);
        }
        h3 { color: #1a1a2e; margin-bottom: 1rem; }

        .erreurs {
            background: #fdecea; color: #c0392b;
            border-left: 4px solid #c0392b;
            padding: .75rem 1rem; border-radius: 4px;
            margin-bottom: 1rem; font-size: .85rem;
        }
        .erreurs ul { padding-left: 1rem; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: .7rem 1rem; text-align: left; font-size: .82rem; color: #555; }
        td { padding: .75rem 1rem; border-bottom: 1px solid #f0f0f0; font-size: .87rem; }
        tr:last-child td { border: none; }

        label { display: block; font-size: .82rem; font-weight: 600; color: #444; margin-bottom: .3rem; }
        input, select {
            width: 100%; padding: .6rem .9rem;
            border: 1.5px solid #ddd; border-radius: 6px;
            font-size: .88rem; margin-bottom: .9rem;
        }
        input:focus, select:focus { outline: none; border-color: #4361ee; }

        .role-fields { display: none; }
        .role-fields.visible { display: block; }

        .btn {
            width: 100%; padding: .75rem;
            background: #4361ee; color: #fff; border: none;
            border-radius: 6px; font-size: .92rem;
            font-weight: 600; cursor: pointer;
        }
        .btn:hover { background: #3451d1; }
        .btn-danger {
            padding: .25rem .65rem; background: #f8d7da;
            color: #842029; border: none; border-radius: 4px;
            font-size: .78rem; cursor: pointer;
        }
        .btn-danger:hover { background: #dc3545; color: #fff; }
    </style>
</head>
<body>

<header>
    <strong>👥 Gestion des utilisateurs</strong>
    <nav>
        <a href="index.php?url=admin/dashboard">Dashboard</a>
        <a href="index.php?url=admin/users">Utilisateurs</a>
        <a href="index.php?url=auth/logout">Déconnexion</a>
    </nav>
</header>

<div class="container">

    <!-- Liste -->
    <div class="card">
        <h3>Tous les utilisateurs</h3>
        <table>
            <thead>
                <tr><th>Nom</th><th>Email</th><th>Inscription</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['date_inscription']) ?></td>
                    <td>
                        <?php if ($u['idUser'] !== $user['idUser']): ?>
                        <form method="POST"
                              action="index.php?url=admin/users/supprimer/<?= $u['idUser'] ?>"
                              onsubmit="return confirm('Supprimer cet utilisateur ?')">
                            <button class="btn-danger">Supprimer</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Formulaire création -->
    <div class="card">
        <h3>Créer un compte</h3>

        <?php if (!empty($erreurs)): ?>
            <div class="erreurs">
                <ul><?php foreach ($erreurs as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?url=admin/users/creer">
            <label>Nom</label>
            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label>Mot de passe</label>
            <input type="password" name="password" required>

            <label>Rôle</label>
            <select name="role" id="role-select" onchange="showRoleFields(this.value)" required>
                <option value="">-- Choisir --</option>
                <option value="etudiant">Étudiant diplômé</option>
                <option value="consulteur">Étudiant consulteur</option>
                <option value="professeur">Professeur</option>
                <option value="directeur">Directeur d'études</option>
            </select>

            <!-- Champs Étudiant / Consulteur -->
            <div class="role-fields" id="fields-etudiant">
                <label>Niveau</label>
                <select name="niveau">
                    <option value="L3">L3</option>
                    <option value="M2">M2</option>
                </select>
                <label>Filière</label>
                <input type="text" name="filiere" placeholder="Informatique, Gestion...">
            </div>

            <!-- Champs Professeur -->
            <div class="role-fields" id="fields-professeur">
                <label>Spécialité</label>
                <input type="text" name="specialite">
                <label>Grade</label>
                <input type="text" name="grade" placeholder="Maître de conf...">
                <label>Département</label>
                <input type="text" name="departement">
            </div>

            <!-- Champs Directeur -->
            <div class="role-fields" id="fields-directeur">
                <label>Bureau</label>
                <input type="text" name="bureau" placeholder="Bureau 12, Bât A">
            </div>

            <button type="submit" class="btn">Créer le compte</button>
        </form>
    </div>

</div>

<script>
function showRoleFields(role) {
    document.querySelectorAll('.role-fields').forEach(el => el.classList.remove('visible'));
    if (role === 'etudiant' || role === 'consulteur') {
        document.getElementById('fields-etudiant').classList.add('visible');
    } else if (role === 'professeur') {
        document.getElementById('fields-professeur').classList.add('visible');
    } else if (role === 'directeur') {
        document.getElementById('fields-directeur').classList.add('visible');
    }
}
</script>

</body>
</html>