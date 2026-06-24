<?php
// app/views/memoire/detail.php
// Route: GET /memoire/detail/{id}
// Affichage complet + commentaires + likes
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container memoire-detail">
    
    <!-- En-tête -->
    <div class="detail-header">
        <h1><?= htmlspecialchars($memoire['titre']) ?></h1>
        <div class="meta">
            <span class="badge badge-<?= $memoire['statut'] ?>"><?= ucfirst($memoire['statut']) ?></span>
            <span class="date">Créé : <?= date('d/m/Y', strtotime($memoire['date_creation'])) ?></span>
            <?php if ($memoire['date_modification'] != $memoire['date_creation']): ?>
                <span class="date">Modifié : <?= date('d/m/Y', strtotime($memoire['date_modification'])) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-content">
        
        <!-- Contenu principal -->
        <main class="main-content">
            <div class="memoire-body">
                <h2>Résumé</h2>
                <p><?= nl2br(htmlspecialchars($memoire['description'])) ?></p>
                
                <h2>Contenu</h2>
                <div class="memoire-text">
                    <?= nl2br(htmlspecialchars($memoire['contenu'])) ?>
                </div>

                <!-- Fichier attaché -->
                <?php if (!empty($memoire['fichier_path'])): ?>
                    <div class="file-section">
                        <h3>📎 Fichier attaché</h3>
                        <a href="<?= $memoire['fichier_path'] ?>" class="btn btn-secondary" download>
                            📥 Télécharger
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Commentaires -->
            <section class="comments-section">
                <h2>Commentaires (<?= count($commentaires) ?>)</h2>
                
                <!-- Formulaire ajout commentaire -->
                <?php if (isset($userId)): ?>
                    <div class="comment-form">
                        <form id="form-commentaire" method="POST" action="/commentaire/add">
                            <input type="hidden" name="memoire_id" value="<?= $memoire['id'] ?>">
                            <textarea name="contenu" placeholder="Ajouter un commentaire..." required></textarea>
                            <button type="submit" class="btn btn-primary">Commenter</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Liste commentaires -->
                <div id="comments-list" class="comments-list">
                    <?php foreach ($commentaires as $comment): ?>
                        <div class="comment" data-comment-id="<?= $comment['id'] ?>">
                            <div class="comment-header">
                                <strong><?= htmlspecialchars($comment['user_name']) ?></strong>
                                <small><?= date('d/m/Y H:i', strtotime($comment['date_creation'])) ?></small>
                            </div>
                            <p class="comment-text"><?= nl2br(htmlspecialchars($comment['contenu'])) ?></p>
                            
                            <?php if ($userId == $comment['user_id'] || $userRole === 'directeur_etudes'): ?>
                                <button class="btn-delete-comment" data-comment-id="<?= $comment['id'] ?>">
                                    Supprimer
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($commentaires)): ?>
                    <p class="no-comments">Aucun commentaire pour le moment.</p>
                <?php endif; ?>
            </section>
        </main>

        <!-- Sidebar -->
        <aside class="sidebar">
            
            <!-- Auteur -->
            <div class="card author-card">
                <h3>Auteur</h3>
                <div class="author-info">
                    <img src="/uploads/avatars/<?= $memoire['user_id'] ?>.jpg" alt="Avatar" class="avatar">
                    <div>
                        <p class="author-name"><?= htmlspecialchars($memoire['auteur_nom']) ?></p>
                        <small><?= htmlspecialchars($memoire['auteur_role']) ?></small>
                    </div>
                </div>
            </div>

            <!-- Likes -->
            <div class="card likes-card">
                <button class="like-btn" data-memoire-id="<?= $memoire['id'] ?>" data-liked="<?= $userHasLiked ? 'true' : 'false' ?>">
                    ❤️ <span class="likes-count"><?= $likesCount ?></span>
                </button>
                <p class="text-muted">J'aime</p>
            </div>

            <!-- Actions -->
            <div class="card actions-card">
                <h3>Actions</h3>
                <div class="dropdown">
                    <button class="btn btn-secondary">⋮ Plus</button>
                    <div class="dropdown-menu">
                        <?php if ($userId == $memoire['user_id'] && $memoire['statut'] === 'brouillon'): ?>
                            <a href="/memoire/modifier/<?= $memoire['id'] ?>">✏️ Modifier</a>
                        <?php endif; ?>
                        
                        <?php if (in_array($userRole, ['professeur', 'directeur_etudes'])): ?>
                            <a href="/memoire/approuver/<?= $memoire['id'] ?>">✅ Approuver</a>
                            <a href="/memoire/rejeter/<?= $memoire['id'] ?>">❌ Rejeter</a>
                        <?php endif; ?>
                        
                        <?php if ($userId == $memoire['user_id']): ?>
                            <a href="#" class="btn-danger">🗑️ Supprimer</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<script src="/js/commentaire.js"></script>
<script src="/js/like.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>