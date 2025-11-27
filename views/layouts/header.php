<?php
// views/layouts/header.php

// Получаем текущую тему из cookies
$current_theme = $_COOKIE['bs_theme'] ?? 'default';
$available_themes = [
    'default' => 'Bootstrap Default',
    'cerulean' => 'Cerulean',
    'cosmo' => 'Cosmo', 
    'cyborg' => 'Cyborg',
    'darkly' => 'Darkly',
    'flatly' => 'Flatly',
    'journal' => 'Journal',
    'litera' => 'Litera',
    'lumen' => 'Lumen',
    'lux' => 'Lux',
    'materia' => 'Materia',
    'minty' => 'Minty',
    'morph' => 'Morph',
    'pulse' => 'Pulse',
    'quartz' => 'Quartz',
    'sandstone' => 'Sandstone',
    'simplex' => 'Simplex',
    'sketchy' => 'Sketchy',
    'slate' => 'Slate',
    'solar' => 'Solar',
    'spacelab' => 'Spacelab',
    'superhero' => 'Superhero',
    'united' => 'United',
    'vapor' => 'Vapor',
    'yeti' => 'Yeti',
    'zephyr' => 'Zephyr'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Web Writer') ?></title>
    
    <!-- Подключаем выбранную тему Bootstrap -->
    <?php if ($current_theme === 'default'): ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php else: ?>
        <link href="<?= SITE_URL ?>/assets/bs/<?= e($current_theme) ?>/bootstrap.min.css" rel="stylesheet">
    <?php endif; ?>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link href="<?= SITE_URL ?>/assets/css/quill.snow.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: 600; }
        .dropdown-menu { min-width: 200px; }
        .alert { border: none; border-radius: 8px; }
        .main-container { min-height: calc(100vh - 120px); }
        .theme-option { padding: 8px 12px; cursor: pointer; }
        .theme-option:hover { background-color: var(--bs-light); }
        .theme-option.active { background-color: var(--bs-primary); color: white; }
        .theme-preview { width: 20px; height: 20px; border-radius: 3px; display: inline-block; margin-right: 8px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= SITE_URL ?>/"><?= e(APP_NAME) ?></a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/dashboard">
                                <i class="bi bi-speedometer2"></i> Панель управления
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/books">
                                <i class="bi bi-journal-bookmark"></i> Мои книги
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/series">
                                <i class="bi bi-collection"></i> Серии
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Выбор темы -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-palette"></i> Тема
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Выбор темы</h6></li>
                            <?php foreach ($available_themes as $theme_key => $theme_name): ?>
                                <li>
                                    <a class="dropdown-item theme-option <?= $current_theme === $theme_key ? 'active' : '' ?>" 
                                       href="#" 
                                       onclick="setTheme('<?= $theme_key ?>')">
                                        <span class="theme-preview" style="background-color: getThemeColor('<?= $theme_key ?>')"></span>
                                        <?= e($theme_name) ?>
                                        <?php if ($current_theme === $theme_key): ?>
                                            <i class="bi bi-check float-end"></i>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= e($_SESSION['display_name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/profile">
                                    <i class="bi bi-gear"></i> Профиль
                                </a></li>
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/author/<?= $_SESSION['user_id'] ?>" target="_blank">
                                    <i class="bi bi-eye"></i> Публичная страница
                                </a></li>
                                <?php if ($_SESSION['user_id'] == 1): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/users">
                                        <i class="bi bi-people"></i> Управление пользователями
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout">
                                    <i class="bi bi-box-arrow-right"></i> Выход
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/login">
                                <i class="bi bi-box-arrow-in-right"></i> Вход
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/register">
                                <i class="bi bi-person-plus"></i> Регистрация
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-container">
        <div class="container mt-4">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= e($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= e($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning alert-dismissible fade show">
                    <?= e($_SESSION['warning']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <?php unset($_SESSION['warning']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['info'])): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <?= e($_SESSION['info']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <?php unset($_SESSION['info']); ?>
                </div>
            <?php endif; ?>