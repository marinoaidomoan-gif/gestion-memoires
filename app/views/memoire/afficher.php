<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($memoire['titre']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; }

        header {
            background: #1a1a2e; color: #fff;
            padding: 1rem 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        header a { color: #aaa; text-decoration: none; font-size: .9rem; }

        .container { max-width: 860px; margin: 2rem auto; padding: 0 1rem; }

        .card {
            background: #fff; border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            padding: 2rem; margin-bottom: 1.5rem;
        }

        h2 { color: #1a1a2e; margin-bottom: .5rem; }
        h3 { color: #1a1a2e; margin-bottom: 1rem; font-size: 1.1rem; }

        .meta { color: #666; font-size: .88rem; margin-bottom: 1rem; }
        .meta span { margin-right: 1.2rem; }

        .badge {
            padding: .3rem .7rem; border-radius: 20px;
            font-size: .78rem; font-weight: 600;
        }
        .badge-attente { background: #fff3cd; color: #856404; }
        .badge-valide  { background: #d1e7dd; color: #0a3622; }
        .badge-rejete  { background: #f8d7da; color: #842029; }

        .info-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: .8rem; margin-top: 1rem; font-size: .9rem;
        }
        .info-grid div { background: #f8f9fa; padding: .6rem 1rem; border-radius: 6px; }
        .info-grid strong { display: block; font-size: .75rem; color: #888; }

        /* Like bouton mémoire */
        .like-section {
            display: flex; align-items: center; gap: .8rem;
            margin-top: 1.2rem; padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }
        .btn-like {
            padding: .5rem 1.2rem; border-radius: 20px;
            border: 1.5px solid #4361ee; background: transparent;
            color: #4361ee; font-size: .88rem; cursor: pointer;
            transition: all .2s;
        }
        .btn-like.liked { background: #4361ee; color: #fff; }
        .btn-like:hover { background: #4361ee; color: #fff; }

        /* Commentaires */
        .comment {
            padding: 1rem 0; border-bottom: 1px solid #f0f0f0;
            display: flex; flex-direction: column; gap: .4rem;
        }
        .comment:last-child { border-bottom: none; }

        .comment-header {
            display: flex; justify-content: space-between;
            align-items: center; font-size: .85rem;
        }
        .comment-author { font-weight: 600; color: #333; }
        .comment-date   { color: #aaa; font-size: .8rem; }
        .comment-body   { color: #444; font-size: .92rem; line-height: 1.5; }
        .comment-edited { font-size: .75rem; color: #aaa; font-style: italic; }

        .comment-actions { display: flex; gap: .5rem; margin-top: .3rem; align-items: center; }

        .btn-sm {
            padding: .25rem .65rem; font-size: .78rem;
            border-radius: 4px; border: none; cursor: pointer;
        }
        .btn-edit    { background: #e9ecef; color: #333; }
        .btn-delete  { background: #f8d7da; color: #842029; }
        .btn-like-sm {
            background: transparent; border: 1px solid #aaa;
            color: #555; border-radius: 20px;
            padding: .2rem .6rem; font-size: .78rem; cursor: pointer;
        }
        .btn-like-sm.liked { border-color: #4361ee; color: #4361ee; }

        /* Formulaire commentaire */
        textarea {
            width: 100%; padding: .75rem 1rem;
            border: 1.5px solid #ddd; border-radius: 6px;
            font-size: .9rem; resize: vertical; min-height: 80px;
            margin-bottom: .8rem;
        }
        textarea:focus { outline: none; border-color: #4361ee; }
        .btn-primary {
            padding: .6rem 1.4rem; background: #4361ee;
            color: #fff; border: none; border-radius: 6px;
            font-size: .9rem; cursor: pointer;
        }
        .btn-primary:hover { background: #3451d1; }

        /* Inline edit form */
        .edit-form { display: none; margin-top: .5rem; }
        .edit-form.visible { display: block; }
    </style>
</head>
<body>

<header>
    <strong>📄 Détail du mémoire</strong>
    <div>
        <?= htmlspecialchars($user['name']) ?> |
        <a href="index.php?url=memoire">← Retour à la liste</a> |
        <a href="index.php?url=auth/logout">Déconnexion</a>
    </div>
</header>

<div class="container">

    <!-- Infos mémoire -->
    <div class="card">
        <h2><?= htmlspecialchars($memoire['titre']) ?></h2>

        <?php
        $badges = ['en_attente'=>'badge-attente','valide'=>'badge-valide','rejete'=>'badge-rejete'];
        $labels = ['en_attente'=>'En attente','valide'=>'Validé','rejete'=>'Rejeté'];
        $s = $memoire['statut'];
        ?>
        <span class="badge <?= $badges[$s] ?>"><?= $labels[$s] ?></span>

        <div class="info-grid">
            <div><strong>Thème</strong><?= htmlspecialchars($memoire['theme']) ?></div>
            <div><strong>Étudiant</strong><?= htmlspecialchars($memoire['nom_etudiant']) ?></div>
            <div><strong>Filière</strong><?= htmlspecialchars($memoire['filiere']) ?></div>
            <div><strong>Niveau</strong><?= htmlspecialchars($memoire['niveau']) ?></div>
            <div><strong>Encadreur</strong><?= htmlspecialchars($memoire['nom_professeur'] ?? 'Non assigné') ?></div>
            <div><strong>Directeur</strong><?= htmlspecialchars($memoire['nom_directeur'] ?? 'Non assigné') ?></div>
            <div><strong>Pages</strong><?= $memoire['nbPages'] ?? '-' ?></div>
            <div><strong>Soumis le</strong><?= htmlspecialchars($memoire['date_soumission']) ?></div>
        </div>

        <?php if ($memoire['fichier']): ?>
            <p style="margin-top:1rem">
                <?php if ($memoire['fichier']): ?>
<div style="margin-top:1rem">
    <h3 style="margin-bottom:.8rem">📖 Lire le mémoire</h3>
    <iframe
        src="index.php?url=memoire/lire/<?= $memoire['idMemoire'] ?>"
        width="100%"
        height="700px"
        style="border:1px solid #ddd; border-radius:8px">
    </iframe>
</div>
<?php endif; ?>
            </p>
        <?php endif; ?>

        <!-- Like mémoire -->
        <div class="like-section">
            <button
                class="btn-like <?= $memoire['user_liked_memoire'] ? 'liked' : '' ?>"
                data-type="memoire"
                data-id="<?= $memoire['idMemoire'] ?>"
                id="like-memoire-btn">
                ♥ J'aime
            </button>
            <span id="like-memoire-count"><?= $memoire['nb_likes_memoire'] ?? 0 ?></span> like(s)
        </div>
    </div>

    <!-- Commentaires -->
    <div class="card">
        <h3>💬 Commentaires (<?= count($commentaires) ?>)</h3>

        <?php if (empty($commentaires)): ?>
            <p style="color:#888;font-size:.9rem">Aucun commentaire pour l'instant.</p>
        <?php else: ?>
            <?php foreach ($commentaires as $c): ?>
            <div class="comment" id="comment-<?= $c['idCommentaire'] ?>">
                <div class="comment-header">
                    <span class="comment-author"><?= htmlspecialchars($c['nom_auteur']) ?></span>
                    <span class="comment-date"><?= htmlspecialchars($c['date_comment']) ?></span>
                </div>
                <div class="comment-body"><?= nl2br(htmlspecialchars($c['contenu'])) ?></div>
                <?php if ($c['estModifie']): ?>
                    <span class="comment-edited">modifié</span>
                <?php endif; ?>

                <div class="comment-actions">
                    <!-- Like commentaire -->
                    <button
                        class="btn-like-sm"
                        data-type="commentaire"
                        data-id="<?= $c['idCommentaire'] ?>">
                        ♥ <span class="like-count"><?= $c['nb_likes'] ?? 0 ?></span>
                    </button>

                    <?php if ($user['idUser'] == $c['idUser']): ?>
                        <button class="btn-sm btn-edit"
                            onclick="toggleEdit(<?= $c['idCommentaire'] ?>)">Modifier</button>
                        <form method="POST" action="index.php?url=commentaire/supprimer/<?= $c['idCommentaire'] ?>"
                              style="display:inline"
                              onsubmit="return confirm('Supprimer ce commentaire ?')">
                            <input type="hidden" name="idMemoire" value="<?= $memoire['idMemoire'] ?>">
                            <button class="btn-sm btn-delete">Supprimer</button>
                        </form>
                    <?php elseif ($user['role'] === 'directeur'): ?>
                        <form method="POST" action="index.php?url=commentaire/supprimer/<?= $c['idCommentaire'] ?>"
                              style="display:inline"
                              onsubmit="return confirm('Supprimer ce commentaire ?')">
                            <input type="hidden" name="idMemoire" value="<?= $memoire['idMemoire'] ?>">
                            <button class="btn-sm btn-delete">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Formulaire modification inline -->
                <?php if ($user['idUser'] == $c['idUser']): ?>
                <div class="edit-form" id="edit-form-<?= $c['idCommentaire'] ?>">
                    <form method="POST" action="index.php?url=commentaire/modifier/<?= $c['idCommentaire'] ?>">
                        <input type="hidden" name="idMemoire" value="<?= $memoire['idMemoire'] ?>">
                        <textarea name="contenu"><?= htmlspecialchars($c['contenu']) ?></textarea>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                        <button type="button" class="btn-sm btn-edit"
                            onclick="toggleEdit(<?= $c['idCommentaire'] ?>)">Annuler</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Ajouter un commentaire -->
        <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid #f0f0f0">
            <h3 style="margin-bottom:.8rem">Laisser un commentaire</h3>
            <form method="POST" action="index.php?url=commentaire/ajouter">
                <input type="hidden" name="idMemoire" value="<?= $memoire['idMemoire'] ?>">
                <textarea name="contenu" placeholder="Votre commentaire..." required></textarea>
                <button type="submit" class="btn-primary">Publier</button>
            </form>
        </div>
    </div>

</div>

<script>
// Toggle formulaire de modification
function toggleEdit(id) {
    const form = document.getElementById('edit-form-' + id);
    form.classList.toggle('visible');
}

// Like (AJAX)
document.querySelectorAll('[data-type]').forEach(btn => {
    btn.addEventListener('click', async () => {
        const type = btn.dataset.type;
        const id   = btn.dataset.id;

        const res  = await fetch('index.php?url=like/toggle', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `type=${type}&id=${id}`
        });
        const data = await res.json();

        if (type === 'memoire') {
            btn.classList.toggle('liked', data.action === 'liked');
            document.getElementById('like-memoire-count').textContent = data.count;
        } else {
            btn.classList.toggle('liked', data.action === 'liked');
            btn.querySelector('.like-count').textContent = data.count;
        }
    });
});
</script>

</body>
</html>