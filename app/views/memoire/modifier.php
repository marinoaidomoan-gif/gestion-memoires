<?php
// app/views/memoire/modifier.php
// Route: GET/POST /memoire/modifier/{id}
// Édition d'un mémoire (propriétaire seulement)
?>

<?php include APP_ROOT . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="form-wrapper">
        <h1>Modifier le mémoire</h1>

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

        <?php if ($memoire['statut'] !== 'brouillon'): ?>
            <div class="alert alert-warning">
                <strong>ℹ️ Attention :</strong> Ce mémoire a déjà été soumis. Vous pouvez demander une révision, 
                mais le contenu actuel est gelé jusqu'à approbation.
            </div>
        <?php endif; ?>

        <form method="POST" action="/memoire/modifier/<?= $memoire['id'] ?>" enctype="multipart/form-data" class="memoire-form">
            
            <!-- Statut actuel -->
            <div class="info-box">
                <p><strong>Statut actuel :</strong> 
                    <span class="badge badge-<?= $memoire['statut'] ?>"><?= ucfirst($memoire['statut']) ?></span>
                </p>
                <p><strong>Créé :</strong> <?= date('d/m/Y H:i', strtotime($memoire['date_creation'])) ?></p>
                <p><strong>Dernière modification :</strong> <?= date('d/m/Y H:i', strtotime($memoire['date_modification'])) ?></p>
            </div>

            <!-- Titre -->
            <div class="form-group">
                <label for="titre">Titre du mémoire *</label>
                <input type="text" 
                       id="titre" 
                       name="titre" 
                       maxlength="255" 
                       required 
                       value="<?= htmlspecialchars($memoire['titre']) ?>"
                       <?= $memoire['statut'] !== 'brouillon' ? 'readonly' : '' ?>>
            </div>

            <!-- Description courte -->
            <div class="form-group">
                <label for="description">Résumé *</label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          maxlength="500" 
                          required
                          <?= $memoire['statut'] !== 'brouillon' ? 'readonly' : '' ?>><?= htmlspecialchars($memoire['description']) ?></textarea>
            </div>

            <!-- Contenu principal -->
            <div class="form-group">
                <label for="contenu">Contenu du mémoire *</label>
                <textarea id="contenu" 
                          name="contenu" 
                          rows="15" 
                          required
                          <?= $memoire['statut'] !== 'brouillon' ? 'readonly' : '' ?>><?= htmlspecialchars($memoire['contenu']) ?></textarea>
            </div>

            <!-- Catégorie -->
            <div class="form-group">
                <label for="categorie">Domaine/Catégorie *</label>
                <select id="categorie" name="categorie" required <?= $memoire['statut'] !== 'brouillon' ? 'disabled' : '' ?>>
                    <option value="informatique" <?= $memoire['categorie'] === 'informatique' ? 'selected' : '' ?>>Informatique</option>
                    <option value="systemes" <?= $memoire['categorie'] === 'systemes' ? 'selected' : '' ?>>Systèmes & Réseaux</option>
                    <option value="logiciels" <?= $memoire['categorie'] === 'logiciels' ? 'selected' : '' ?>>Logiciels</option>
                    <option value="autre" <?= $memoire['categorie'] === 'autre' ? 'selected' : '' ?>>Autre</option>
                </select>
            </div>

            <!-- Mots-clés -->
            <div class="form-group">
                <label for="mots_cles">Mots-clés (séparés par virgule)</label>
                <input type="text" 
                       id="mots_cles" 
                       name="mots_cles" 
                       value="<?= htmlspecialchars($memoire['mots_cles'] ?? '') ?>">
            </div>

            <!-- Fichier attaché -->
            <div class="form-group">
                <label>Fichier actuel</label>
                <?php if (!empty($memoire['fichier_path'])): ?>
                    <div class="current-file">
                        <p>📄 <?= basename($memoire['fichier_path']) ?></p>
                        <label>
                            <input type="checkbox" name="supprimer_fichier"> Supprimer ce fichier
                        </label>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Aucun fichier attaché</p>
                <?php endif; ?>
            </div>

            <!-- Nouveau fichier -->
            <div class="form-group">
                <label for="fichier">Remplacer par un nouveau fichier (optionnel)</label>
                <input type="file" 
                       id="fichier" 
                       name="fichier" 
                       accept=".pdf,.doc,.docx,.txt"
                       onchange="validateFile(this)">
                <small>10MB max • Formats : PDF, Word, Texte</small>
            </div>

            <!-- Section historique -->
            <?php if (!empty($historique)): ?>
                <div class="historique-section">
                    <h3>Historique des modifications</h3>
                    <div class="timeline">
                        <?php foreach ($historique as $version): ?>
                            <div class="timeline-item">
                                <small><?= date('d/m/Y H:i', strtotime($version['date'])) ?></small>
                                <p><?= htmlspecialchars($version['action']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Boutons -->
            <div class="form-actions">
                <a href="/memoire/detail/<?= $memoire['id'] ?>" class="btn btn-outline">← Retour</a>
                
                <?php if ($memoire['statut'] === 'brouillon'): ?>
                    <button type="submit" name="action" value="brouillon" class="btn btn-secondary">
                        💾 Sauvegarder brouillon
                    </button>
                    <button type="submit" name="action" value="soumis" class="btn btn-primary">
                        ✅ Soumettre
                    </button>
                <?php else: ?>
                    <button type="submit" name="action" value="revision" class="btn btn-primary">
                        🔄 Demander révision
                    </button>
                <?php endif; ?>
                
                <a href="/memoire/detail/<?= $memoire['id'] ?>" class="btn btn-outline">Annuler</a>
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