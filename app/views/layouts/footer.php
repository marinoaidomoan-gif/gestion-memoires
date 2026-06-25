<?php
// app/views/layouts/footer.php
$estConnecte = isset($_SESSION['idUser']);
?>

        </main>
        <!-- /main-content ou main-public -->

    <?php if ($estConnecte): ?>
        </div>
        <!-- /app-layout -->
    <?php endif; ?>

</div><!-- /wrapper global si besoin -->

<!-- ===================== FOOTER ===================== -->
<footer class="site-footer">
    <div class="footer-inner">
        <p class="footer-brand">
            <i class="fa-solid fa-book-open"></i>
            <strong>GestMémoire</strong>
        </p>
        <p class="footer-copy">
            &copy; <?= date('Y') ?> UATM/GASA Formation &mdash; Tous droits réservés.
        </p>
        <div class="footer-links">
            <a href="/gestion_memoires/public/index.php?route=memoires">
                <i class="fa-solid fa-layer-group"></i> Mémoires
            </a>
            <?php if ($estConnecte): ?>
                <a href="/gestion_memoires/public/index.php?route=auth/logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                </a>
            <?php else: ?>
                <a href="/gestion_memoires/public/index.php?route=auth/login">
                    <i class="fa-solid fa-right-to-bracket"></i> Connexion
                </a>
            <?php endif; ?>
        </div>
    </div>
</footer>

<!-- ===================== SCRIPTS ===================== -->

<!-- Dropdown navbar -->
<script>
(function () {
    const btn  = document.getElementById('user-dropdown-btn');
    const menu = document.getElementById('user-dropdown-menu');

    if (btn && menu) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.classList.toggle('open');
        });
        document.addEventListener('click', function () {
            menu.classList.remove('open');
        });
    }

    // Hamburger mobile
    const toggle    = document.getElementById('navbar-toggle');
    const navMenu   = document.getElementById('navbar-menu');
    if (toggle && navMenu) {
        toggle.addEventListener('click', function () {
            navMenu.classList.toggle('open');
        });
    }
})();
</script>

<!-- Collapse sidebar -->
<script>
(function () {
    const sidebar      = document.getElementById('sidebar');
    const collapseBtn  = document.getElementById('sidebar-collapse-btn');
    const collapseIcon = document.getElementById('sidebar-collapse-icon');
    const mainContent  = document.getElementById('main-content');

    if (!collapseBtn || !sidebar) return;

    const COLLAPSED_KEY = 'sidebar_collapsed';
    const isCollapsed   = localStorage.getItem(COLLAPSED_KEY) === 'true';

    function applyState(collapsed) {
        sidebar.classList.toggle('collapsed', collapsed);
        if (mainContent) mainContent.classList.toggle('sidebar-collapsed', collapsed);
        if (collapseIcon) {
            collapseIcon.className = collapsed
                ? 'fa-solid fa-angles-right'
                : 'fa-solid fa-angles-left';
        }
    }

    applyState(isCollapsed);

    collapseBtn.addEventListener('click', function () {
        const nowCollapsed = !sidebar.classList.contains('collapsed');
        applyState(nowCollapsed);
        localStorage.setItem(COLLAPSED_KEY, nowCollapsed);
    });
})();
</script>

<!-- Flash auto-dismiss -->
<script>
(function () {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(function () {
            flash.style.opacity = '0';
            setTimeout(function () { flash.remove(); }, 400);
        }, 4000);
    }
})();
</script>

<!-- Scripts spécifiques à la page -->
<?php if (isset($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="/public/js/<?= htmlspecialchars($js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>