<?php
// views/errors/404.php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="py-5">
                <div class="mb-4">
                    <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
                </div>
                <h1 class="display-4 fw-bold text-muted">404</h1>
                <h2 class="h3 mb-4">Страница не найдена</h2>
                <p class="lead text-muted mb-5">
                    Запрашиваемая страница не существует или была перемещена.
                </p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="<?= SITE_URL ?>/" class="btn btn-primary btn-lg">
                        <i class="bi bi-house"></i> На главную
                    </a>
                    <a href="<?= SITE_URL ?>/books" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-journal-bookmark"></i> К книгам
                    </a>
                    <?php if (!is_logged_in()): ?>
                        <a href="<?= SITE_URL ?>/login" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Войти
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>