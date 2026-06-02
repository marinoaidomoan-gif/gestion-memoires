<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Gestion des Mémoires</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
            width: 100%;
            max-width: 420px;
        }

        h1 {
            font-size: 1.6rem;
            color: #1a1a2e;
            margin-bottom: .4rem;
        }

        .subtitle {
            color: #666;
            font-size: .9rem;
            margin-bottom: 2rem;
        }

        .error {
            background: #fdecea;
            color: #c0392b;
            border-left: 4px solid #c0392b;
            padding: .75rem 1rem;
            border-radius: 4px;
            margin-bottom: 1.2rem;
            font-size: .9rem;
        }

        label {
            display: block;
            font-size: .85rem;
            font-weight: 600;
            color: #444;
            margin-bottom: .4rem;
        }

        input {
            width: 100%;
            padding: .75rem 1rem;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            font-size: .95rem;
            margin-bottom: 1.2rem;
            transition: border-color .2s;
        }

        input:focus {
            outline: none;
            border-color: #4361ee;
        }

        button {
            width: 100%;
            padding: .85rem;
            background: #4361ee;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }

        button:hover { background: #3451d1; }
    </style>
</head>
<body>

<div class="card">
    <h1>Connexion</h1>
    <p class="subtitle">Gestion des mémoires — UATM</p>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?url=auth/login">
        <label for="email">Adresse email</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="vous@uatm.com"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            required
            autofocus
        >

        <label for="password">Mot de passe</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="••••••••"
            required
        >

        <button type="submit">Se connecter</button>
    </form>
</div>

</body>
</html>