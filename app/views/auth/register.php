<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="main-public">
    <div class="container" style="max-width: 500px;">
        <div class="card">
            <h2><i class="fas fa-user-plus"></i> Créer un compte</h2>
            
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

                <div class="form-actions mt-1">
                    <button type="submit" class="btn btn-primary">S'inscrire</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
