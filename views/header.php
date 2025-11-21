<?php
// views/header.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(APP_NAME) ?> - <?= e($page_title ?? 'Платформа для писателей') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1.5.10/css/pico.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/foundation-icons.css" />
</head>
<body>
    <nav class="container-fluid black">
    <ul>
        <li><strong><a href="/" style="text-decoration: none;"><?= e(APP_NAME) ?></a></strong></li>
    </ul>
    <ul>
        <?php if (is_logged_in()): ?>
            <li><a href="/dashboard.php">📊 Панель</a></li>
            <li><a href="/series.php">📚 Мои серии</a></li>
            <li><a href="/books.php">📚 Мои книги</a></li>
            <li>
                <details role="list" dir="rtl">
                    <summary aria-haspopup="listbox" role="link" style="display: flex; align-items: center; gap: 0.5rem;">
                        <?php if (!empty($_SESSION['avatar'])): ?>
                            <img src="<?= AVATARS_URL . e($_SESSION['avatar']) ?>" 
                                alt="Аватар" 
                                style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;"
                                onerror="this.style.display='none'">
                        <?php endif; ?>
                        👤 <?= e($_SESSION['display_name']) ?>
                    </summary>
                    <ul role="listbox">
                        <li><a href="/profile.php">Настройки профиля</a></li>
                        <li><a href="/author.php?id=<?= $_SESSION['user_id'] ?>" target="_blank">Моя публичная страница</a></li>
                        <?php if ($_SESSION['user_id'] == 1): ?>
                            <li><a href="/admin/users.php">👥 Пользователи</a></li>
                        <?php endif; ?>
                        <li><a href="/logout.php">Выйти</a></li>
                    </ul>
                </details>
            </li>
        <?php else: ?>
            <li><a href="/login.php">Войти</a></li>
            <li><a href="/register.php">Регистрация</a></li>
        <?php endif; ?>
    </ul>
</nav>
    <main class="container">