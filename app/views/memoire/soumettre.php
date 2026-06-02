<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Soumettre un mémoire</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; }

        header {
            background: #1a1a2e; color: #fff;
            padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        header a { color: #aaa; text-decoration: none; font-size: .9rem; }

        .container { max-width: 680px; margin: 2rem auto; padding: 0 1rem; }

        .card {
            background: #fff; padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }

        h2 { margin-bottom: 1.5rem; color: #1a1a2e; }

        .erreurs {
            background: #fdecea; color: #c0392b;
            border-left: 4px solid #c0392b;
            padding: .8rem 1rem; border-radius: 4px;
            margin-bottom: 1.2rem; font-size: .88rem;
        }
        .erreurs ul { padding-left: 1rem; }

        label { display: block; font-size: .85rem; font-weight: 600; color: #444; margin-bottom: .35rem; }

        input, textarea, select {
            width: 100%; padding: .7rem 1rem;
            border: 1.5px solid #ddd; border-radius: 6px;
            font-size: .9rem; margin-bottom: 1.1rem;
        }
        input:focus, textarea:focus { outline: none; border-color: #4361ee; }

        textarea { resize: vertical; min-height: 80px; }

        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        .btn {
            padding: .8rem 1.6rem; background: #4361ee;
            color: #fff; border: none; border-radius: 6px;
            font-size: .95rem; font-weight: 600; cursor: pointer;
        }
        .btn:hover { background: #3451d1; }
        .btn-outline {
            background: transparent; color: #4361ee;
            border: 1.5px solid #4361ee; margin-right: .5rem;
            text-decoration: none; display: inline-block; padding: .75rem 1.4rem;
            border-radius: 6px; font-size: .95rem;
        }
    </style>
</head>
<body>

<header>
    <strong>📄 Soumettre un mémoire</strong>
    <a href="index.php?url=memoire">← Retour</a>
</header>

<div class="container">
<div class="card">
    <h2>Nouveau mémoire</h2>

    <?php if (!empty($erreurs)): ?>
        <div class="erreurs">
            <ul>
                <?php foreach ($erreurs as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?url=memoire/soumettre" enctype="multipart/form-data">

        <label for="titre">Titre *</label>
        <input type="text" id="titre" name="titre"
               value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
               placeholder="Titre de votre mémoire" required>

        <label for="theme">Thème *</label>
        <input type="text" id="theme" name="theme"
               value="<?= htmlspecialchars($_POST['theme'] ?? '') ?>"
               placeholder="Thème principal" required>

        <div class="row">
            <div>
                <label for="nbPages">Nombre de pages *</label>
                <input type="number" id="nbPages" name="nbPages" min="1"
                       value="<?= htmlspecialchars($_POST['nbPages'] ?? '') ?>" required>
            </div>
            <div>
                <label for="centre">Centre / Département</label>
                <input type="text" id="centre" name="centre"
                       value="<?= htmlspecialchars($_POST['centre'] ?? '') ?>">
            </div>
        </div>

        <label for="fichier">Fichier PDF</label>
        <input type="file" id="fichier" name="fichier" accept=".pdf">

        <div style="margin-top:.5rem">
            <a href="index.php?url=memoire" class="btn-outline">Annuler</a>
            <button type="submit" class="btn">Soumettre</button>
        </div>

    </form>
</div>
</div>

</body>
</html>