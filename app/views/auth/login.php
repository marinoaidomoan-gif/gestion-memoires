<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="main-public">
    <div class="container" style="max-width: 500px;">
        <div class="card">
            <h2><i class="fas fa-sign-in-alt"></i> Connexion</h2>
            
            <?php if (isset($_GET['reg']) && $_GET['reg'] === 'success'): ?>
                <div class="alert alert-success mt-2">
                    <i class="fas fa-check-circle"></i> Inscription réussie ! Vous pouvez vous connecter.
                </div>
            <?php endif; ?>

            <form action="/gestion_memoires/public/index.php?route=login" method="POST" class="memoire-form mt-2">
                <div class="form-group">
                    <label>Email :</label>
                    <input type="email" name="email" required placeholder="exemple@uatm.bj">
                </div>

                <div class="form-group">
                    <label>Mot de passe :</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>

                <div class="form-actions mt-1">
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
