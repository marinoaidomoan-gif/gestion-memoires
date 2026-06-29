<?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="container" style="max-width: 500px;">
        <div class="card">
            <h2><i class="fas fa-user-plus"></i> Créer un compte</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger mt-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success mt-2">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form action="/gestion_memoires/public/index.php?route=register" method="POST" class="memoire-form mt-2">
                <div class="form-group">
                    <label>Nom :</label>
                    <input type="text" name="name" required placeholder="Saisir le nom">
                </div>

                <div class="form-group">
                    <label>Prénom :</label>
                    <input type="text" name="prenom" required placeholder="Saisir le prénom">
                </div>

                <div class="form-group">
                    <label>Email :</label>
                    <input type="email" name="email" required placeholder="exemple@uatm.bj">
                </div>

                <div class="form-group">
                    <label>Mot de passe :</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label>Rôle :</label>
                    <select name="role">
                        <option value="etudiant_consulteur">Étudiant Consulteur</option>
                        <option value="etudiant_diplome">Diplômé (Dépôt de mémoire)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Niveau :</label>
                    <select name="niveau" required>
                        <option value="">-- Sélectionner ton niveau --</option>
                        <option value="L1">Licence 1 (L1)</option>
                        <option value="L2">Licence 2 (L2)</option>
                        <option value="L3">Licence 3 (L3)</option>
                    </select>
                </div>

                <div class="form-group mt-1">
                    <label>Filière :</label>
                    <input type="text" name="filiere" required placeholder="Ex: SIL, AGE, RIT...">
                </div>

                <div class="form-actions mt-1">
                    <button type="submit" name="submit" class="btn btn-primary">S'inscrire</button>
                </div>
            </form>
        </div>
    </div>

    <p class="mt-2" style="text-align:center; font-size:0.85rem;">
        Déjà un compte ? 
        <a href="/gestion_memoires/public/index.php?route=login">Se connecter</a>
    </p>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
