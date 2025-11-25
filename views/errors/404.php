<?php
// views/errors/404.php
include 'views/layouts/header.php';
?>

<div class="container" style="text-align: center; padding: 4rem 1rem;">
    <h1>404 - Страница не найдена</h1>
    <p style="font-size: 1.2rem; margin-bottom: 2rem;">
        Запрашиваемая страница не существует или была перемещена.
    </p>
    <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
        <a href="<?= SITE_URL ?>/" class="button">🏠 На главную</a>
        <a href="<?= SITE_URL ?>/books" class="button secondary">📚 К книгам</a>
        <?php if (!is_logged_in()): ?>
            <a href="<?= SITE_URL ?>/login" class="button secondary">🔑 Войти</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>