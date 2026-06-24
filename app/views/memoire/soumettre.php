<?php
// app/views/memoire/soumettre.php
// Route: GET/POST /memoire/soumettre
// Formulaire création nouveau mémoire (Étudiant Diplômé)
?>

<?php include APP_ROOT . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="form-wrapper">
        <h1>Soumettre un nouveau mémoire</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Erreurs :</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/memoire/soumettre" enctype="multipart/form-data" class="memoire-form">
            
            <!-- Titre -->
            <div class="form-group">
                <label for="titre">Titre du mémoire *</label>
                <input type="text" 
                       id="titre" 
                       name="titre" 
                       maxlength="255" 
                       required 
                       value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                       placeholder="Ex: Analyse des systèmes de gestion...">
                <small>255 caractères max</small>
            </div>

            <!-- Description courte -->
            <div class="form-group">
                <label for="description">Résumé *</label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          maxlength="500" 
                          required
                          placeholder="Résumé court du contenu..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                <small>500 caractères max</small>
            </div>

            <!-- Contenu principal -->
            <div class="form-group">
                <label for="contenu">Contenu du mémoire *</label>
                <textarea id="contenu" 
                          name="contenu" 
                          rows="15" 
                          required
                          placeholder="Entrez le contenu complet..."><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
                <small>Pas de limite de caractères</small>
            </div>

            <!-- Catégorie -->
            <div class="form-group">
                <label for="categorie">Domaine/Catégorie *</label>
                <select id="categorie" name="categorie" required>
                    <option value="">-- Sélectionnez --</option>
                    <option value="informatique" <?= ($_POST['categorie'] ?? '') === 'informatique' ? 'selected' : '' ?>>Informatique</option>
                    <option value="systemes" <?= ($_POST['categorie'] ?? '') === 'systemes' ? 'selected' : '' ?>>Systèmes & Réseaux</option>
                    <option value="logiciels" <?= ($_POST['categorie'] ?? '') === 'logiciels' ? 'selected' : '' ?>>Logiciels</option>
                    <option value="autre" <?= ($_POST['categorie'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
                </select>
            </div>

            <!-- Mots-clés -->
            <div class="form-group">
                <label for="mots_cles">Mots-clés (séparés par virgule)</label>
                <input type="text" 
                       id="mots_cles" 
                       name="mots_cles" 
                       placeholder="Ex: PHP, MVC, Gestion..."
                       value="<?= htmlspecialchars($_POST['mots_cles'] ?? '') ?>">
            </div>

            <!-- Upload fichier -->
            <div class="form-group">
                <label for="fichier">Fichier (PDF, DOC, DOCX) - 10MB max</label>
                <input type="file" 
                       id="fichier" 
                       name="fichier" 
                       accept=".pdf,.doc,.docx,.txt"
                       onchange="validateFile(this)">
                <small>Formats acceptés : PDF, Word, Texte</small>
            </div>

            <!-- Boutons -->
            <div class="form-actions">
                <button type="submit" name="action" value="brouillon" class="btn btn-secondary">
                    💾 Sauvegarder en brouillon
                </button>
                <button type="submit" name="action" value="soumis" class="btn btn-primary">
                    ✅ Soumettre
                </button>
                <a href="/memoire" class="btn btn-outline">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function validateFile(input) {
    const file = input.files[0];
    if (file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            alert('Le fichier dépasse 10MB');
            input.value = '';
            return false;
        }
        const validTypes = ['application/pdf', 'application/msword', 
                           'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                           'text/plain'];
        if (!validTypes.includes(file.type)) {
            alert('Type de fichier non autorisé');
            input.value = '';
            return false;
        }
    }
    return true;
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>