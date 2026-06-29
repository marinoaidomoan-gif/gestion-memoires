<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="form-wrapper">
        <h1><i class="fa-solid fa-file-arrow-up"></i> Soumettre un nouveau mémoire</h1>
        <p class="text-muted mb-2">Déposez votre document de fin de cycle dans le catalogue de l'institut.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <strong><i class="fa-solid fa-circle-exclamation"></i> Erreur :</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <strong><i class="fa-solid fa-circle-check"></i> Succès :</strong> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/gestion_memoires/public/index.php?route=memoire/soumettre" enctype="multipart/form-data" class="memoire-form">
            
            <!-- Titre -->
            <div class="form-group">
                <label for="titre">Titre du mémoire *</label>
                <input type="text" id="titre" name="titre" maxlength="255" required 
                       value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                       placeholder="Ex: Analyse et sécurisation des architectures cloud...">
            </div>

            <!-- Thème (Axe de recherche) -->
            <div class="form-group mt-1">
                <label for="theme">Thème / Filière *</label>
                <select id="theme" name="theme" required>
                    <option value="">-- Sélectionnez l'axe de recherche --</option>
                    <option value="Informatique" <?= ($_POST['theme'] ?? '') === 'Informatique' ? 'selected' : '' ?>>Systèmes Informatiques et Logiciels (SIL)</option>
                    <option value="Réseaux" <?= ($_POST['theme'] ?? '') === 'Réseaux' ? 'selected' : '' ?>>Réseaux et Télécommunications (RIT)</option>
                    <option value="Sécurité" <?= ($_POST['theme'] ?? '') === 'Sécurité' ? 'selected' : '' ?>>Sécurité Informatique</option>
                </select>
            </div>

            <!-- Professeur Encadrant (Nouveau champ dynamique Option B) -->
            <div class="form-group mt-1">
                <label for="idProfesseur">Professeur encadrant / Superviseur *</label>
                <select id="idProfesseur" name="idProfesseur" required>
                    <option value="">-- Sélectionnez votre enseignant --</option>
                    <?php if (!empty($professeurs)): ?>
                        <?php foreach ($professeurs as $prof): ?>
                            <option value="<?= $prof['id_user'] ?>" <?= ($_POST['idProfesseur'] ?? '') == $prof['id_user'] ? 'selected' : '' ?>>
                                M./Mme <?= htmlspecialchars($prof['prenom'] . ' ' . $prof['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Nombre de pages -->
            <div class="form-group mt-1">
                <label for="nbPages">Nombre de pages</label>
                <input type="number" id="nbPages" name="nbPages" min="1"
                       value="<?= htmlspecialchars($_POST['nbPages'] ?? '') ?>"
                       placeholder="Ex: 45">
            </div>

            <!-- Centre d'examen / Institut -->
            <div class="form-group mt-1">
                <label for="centre">Institut / Centre d'évaluation *</label>
                <input type="text" id="centre" name="centre" required
                       value="<?= htmlspecialchars($_POST['centre'] ?? 'UATM / GASA Formation') ?>">
            </div>

            <!-- Année académique -->
            <div class="form-group mt-1">
                <label for="annee_academique">Année académique *</label>
                <input type="text" id="annee_academique" name="annee_academique" required 
                       value="<?= htmlspecialchars($_POST['annee_academique'] ?? '') ?>"
                       placeholder="Ex: 2025-2026">
            </div>

            <!-- Upload fichier PDF -->
            <div class="form-group mt-1">
                <label for="fichier">Fichier du mémoire (PDF uniquement) *</label>
                <input type="file" id="fichier" name="fichier" accept=".pdf" required onchange="validateFile(this)">
                <small>Format exigé : PDF (10 Mo maximum)</small>
            </div>

            <!-- Boutons d'action -->
            <div class="form-actions mt-2">
                <!-- Rétablissement du bouton d'origine du contrôleur -->
                <button type="submit" name="action" value="soumis" class="btn btn-primary">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Soumettre le mémoire
                </button>
                <a href="/gestion_memoires/public/index.php?route=memoires" class="btn btn-outline">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function validateFile(input) {
    const file = input.files[0];
    if (file) {
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert("Le fichier dépasse la limite de 10 Mo autorisée.");
            input.value = '';
            return false;
        }
        if (file.type !== 'application/pdf') {
            alert("Seuls les documents au format PDF sont acceptés par l'institut.");
            input.value = '';
            return false;
        }
    }
    return true;
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>