<?php
// views/layouts/header.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Web Writer') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1.5.10/css/pico.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
</head>
<body>
    <nav class="container-fluid">
        <ul>
            <li><strong><a href="<?= SITE_URL ?>/"><?= e(APP_NAME) ?></a></strong></li>
        </ul>
        <ul>
            <?php if (is_logged_in()): ?>
                <li><a href="<?= SITE_URL ?>/dashboard">📊 Панель управления</a></li>
                <li><a href="<?= SITE_URL ?>/books">📚 Мои книги</a></li>
                <li><a href="<?= SITE_URL ?>/series">📑 Серии</a></li>
                <li>
                    <details role="list" dir="rtl">
                        <summary aria-haspopup="listbox" role="link">
                            👤 <?= e($_SESSION['display_name']) ?>
                        </summary>
                        <ul role="listbox">
                            <li><a href="<?= SITE_URL ?>/profile">⚙️ Профиль</a></li>
                            <li><a href="<?= SITE_URL ?>/author/<?= $_SESSION['user_id'] ?>" target="_blank">👤 Моя публичная страница</a></li>
                            <?php if ($_SESSION['user_id'] == 1): // Проверка на администратора ?>
                                <li><a href="<?= SITE_URL ?>/admin/users">👥 Управление пользователями</a></li>
                            <?php endif; ?>
                            <li><a href="<?= SITE_URL ?>/logout">🚪 Выход</a></li>
                        </ul>
                    </details>
                </li>
            <?php else: ?>
                <li><a href="<?= SITE_URL ?>/login">🔑 Вход</a></li>
                <li><a href="<?= SITE_URL ?>/register">📝 Регистрация</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <main class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= e($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= e($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['warning'])): ?>
            <div class="alert alert-warning">
                <?= e($_SESSION['warning']) ?>
                <?php unset($_SESSION['warning']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['info'])): ?>
            <div class="alert alert-info">
                <?= e($_SESSION['info']) ?>
                <?php unset($_SESSION['info']); ?>
            </div>
        <?php endif; ?>