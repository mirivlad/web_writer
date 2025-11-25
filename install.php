<?php
// install.php - установщик приложения

// Проверяем, не установлено ли приложение уже
if (file_exists('config/config.php')) {
    die('Приложение уже установлено. Для переустановки удалите файл config/config.php');
}

// SQL для создания таблиц
$database_sql = <<<SQL
SET FOREIGN_KEY_CHECKS=0;

-- Удаляем существующие таблицы в правильном порядке (сначала дочерние, потом родительские)
DROP TABLE IF EXISTS `user_sessions`;
DROP TABLE IF EXISTS `chapters`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `series`;
DROP TABLE IF EXISTS `users`;

-- Таблица пользователей
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Таблица серий
CREATE TABLE `series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_series_user_id` (`user_id`),
  CONSTRAINT `series_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Таблица книг
CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `cover_image` varchar(500) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `series_id` int(11) DEFAULT NULL,
  `sort_order_in_series` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `share_token` varchar(32) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `share_token` (`share_token`),
  KEY `user_id` (`user_id`),
  KEY `series_id` (`series_id`),
  KEY `idx_sort_order_in_series` (`sort_order_in_series`),
  KEY `idx_books_series_id` (`series_id`),
  KEY `idx_books_sort_order` (`sort_order_in_series`),
  CONSTRAINT `books_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `books_ibfk_2` FOREIGN KEY (`series_id`) REFERENCES `series` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Таблица глав
CREATE TABLE `chapters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `word_count` int(11) DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Таблица сессий пользователей
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

SET FOREIGN_KEY_CHECKS=1;
SQL;

$step = $_GET['step'] ?? '1';
$error = '';
$success = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === '1') {
        // Шаг 1: Проверка подключения к БД
        $db_host = $_POST['db_host'] ?? 'localhost';
        $db_name = $_POST['db_name'] ?? 'writer_app';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';
        
        try {
            // Пытаемся подключиться к MySQL
            $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Пытаемся создать базу данных если не существует
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$db_name`");
            
            // Сохраняем данные в сессии для следующего шага
            session_start();
            $_SESSION['install_data'] = [
                'db_host' => $db_host,
                'db_name' => $db_name,
                'db_user' => $db_user,
                'db_pass' => $db_pass
            ];
            
            header('Location: install.php?step=2');
            exit;
            
        } catch (PDOException $e) {
            $error = "Ошибка подключения к базе данных: " . $e->getMessage();
        }
        
    } elseif ($step === '2') {
        // Шаг 2: Создание администратора
        session_start();
        if (!isset($_SESSION['install_data'])) {
            header('Location: install.php?step=1');
            exit;
        }
        
        $admin_username = $_POST['admin_username'] ?? '';
        $admin_password = $_POST['admin_password'] ?? '';
        $admin_email = $_POST['admin_email'] ?? '';
        $admin_display_name = $_POST['admin_display_name'] ?? $admin_username;
        
        if (empty($admin_username) || empty($admin_password)) {
            $error = 'Имя пользователя и пароль администратора обязательны';
        } else {
            try {
                $db = $_SESSION['install_data'];
                $pdo = new PDO("mysql:host={$db['db_host']};dbname={$db['db_name']}", $db['db_user'], $db['db_pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Создаем таблицы
                $pdo->exec($database_sql);
                
                // Создаем администратора
                $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, display_name, password_hash, email, is_active, created_at) 
                    VALUES (?, ?, ?, ?, 1, NOW())
                ");
                $stmt->execute([$admin_username, $admin_display_name, $password_hash, $admin_email]);
                
                // Создаем config.php
                $config_content = generate_config($db);
                if (file_put_contents('config/config.php', $config_content)) {
                    // Создаем папки для загрузок
                    if (!file_exists('uploads/covers')) {
                        mkdir('uploads/covers', 0755, true);
                    }
                    if (!file_exists('uploads/avatars')) {
                        mkdir('uploads/avatars', 0755, true);
                    }
                    
                    $success = 'Установка завершена успешно!';
                    session_destroy();
                } else {
                    $error = 'Не удалось создать файл config.php. Проверьте права доступа к папке config/';
                }
                
            } catch (PDOException $e) {
                $error = "Ошибка при установке: " . $e->getMessage();
            }
        }
    }
}

function generate_config($db) {
    $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $base_path = str_replace('/install.php', '', $_SERVER['PHP_SELF']);
    $site_url .= $base_path;
    
    return <<<EOT
<?php
// config/config.php - автоматически сгенерирован установщиком
// Подключаем функции
require_once __DIR__ . '/../includes/functions.php';
session_start();

// Настройки базы данных
define('DB_HOST', '{$db['db_host']}');
define('DB_USER', '{$db['db_user']}');
define('DB_PASS', '{$db['db_pass']}');
define('DB_NAME', '{$db['db_name']}');
define('SITE_URL', '{$site_url}');

// Настройки приложения
define('APP_NAME', 'Web Writer');

define('CONTROLLERS_PATH', __DIR__ . '/../controllers/');
define('VIEWS_PATH', __DIR__ . '/../views/');
define('LAYOUTS_PATH', VIEWS_PATH . 'layouts/');

define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('COVERS_PATH', UPLOAD_PATH . 'covers/');
define('COVERS_URL', SITE_URL . '/uploads/covers/');
define('AVATARS_PATH', UPLOAD_PATH . 'avatars/');
define('AVATARS_URL', SITE_URL . '/uploads/avatars/');

// Создаем папку для загрузок, если ее нет
if (!file_exists(COVERS_PATH)) {
    mkdir(COVERS_PATH, 0755, true);
}
if (!file_exists(AVATARS_PATH)) {
    mkdir(AVATARS_PATH, 0755, true);
}

// Подключение к базе данных
try {
    \$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException \$e) {
    error_log("DB Error: " . \$e->getMessage());
    die("Ошибка подключения к базе данных");
}



// Автозагрузка моделей
spl_autoload_register(function (\$class_name) {
    \$model_file = __DIR__ . '/../models/' . \$class_name . '.php';
    if (file_exists(\$model_file)) {
        require_once \$model_file;
    }
});
?>
EOT;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка Web Writer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1.5.10/css/pico.min.css">
    <style>
        .installation-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 20px;
            background: #f0f0f0;
            color: #666;
        }
        .step.active {
            background: #007bff;
            color: white;
        }
        .install-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }
        .button-group a,
        .button-group button {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            text-decoration: none;
            border: 1px solid;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            box-sizing: border-box;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .button-group a {
            background: var(--secondary);
            border-color: var(--secondary);
            color: var(--secondary-inverse);
        }
        .button-group button {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--primary-inverse);
        }
        .button-group a:hover,
        .button-group button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="install-container">
            <h1 style="text-align: center;">Установка Web Writer</h1>
            
            <!-- Шаги установки -->
            <div class="installation-steps">
                <div class="step <?= $step === '1' ? 'active' : '' ?>">1. База данных</div>
                <div class="step <?= $step === '2' ? 'active' : '' ?>">2. Администратор</div>
                <div class="step <?= $step === '3' ? 'active' : '' ?>">3. Завершение</div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                    <div style="margin-top: 1rem;">
                        <a href="index.php" role="button" class="contrast">Перейти к приложению</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <?php if ($step === '1'): ?>
                    <!-- Шаг 1: Настройки базы данных -->
                    <form method="post">
                        <h3>Настройки базы данных</h3>
                        
                        <label for="db_host">
                            Хост БД
                            <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                        </label>
                        
                        <label for="db_name">
                            Имя базы данных
                            <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? 'writer_app') ?>" required>
                        </label>
                        
                        <label for="db_user">
                            Пользователь БД
                            <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required>
                        </label>
                        
                        <label for="db_pass">
                            Пароль БД
                            <input type="password" id="db_pass" name="db_pass" value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>">
                        </label>
                        
                        <button type="submit" class="contrast" style="width: 100%;">Продолжить</button>
                    </form>

                <?php elseif ($step === '2'): ?>
                    <!-- Шаг 2: Создание администратора -->
                    <form method="post">
                        <h3>Создание администратора</h3>
                        <p>Создайте учетную запись администратора для управления приложением.</p>
                        
                        <label for="admin_username">
                            Имя пользователя *
                            <input type="text" id="admin_username" name="admin_username" value="<?= htmlspecialchars($_POST['admin_username'] ?? '') ?>" required>
                        </label>
                        
                        <label for="admin_password">
                            Пароль *
                            <input type="password" id="admin_password" name="admin_password" required>
                        </label>
                        
                        <label for="admin_display_name">
                            Отображаемое имя
                            <input type="text" id="admin_display_name" name="admin_display_name" value="<?= htmlspecialchars($_POST['admin_display_name'] ?? '') ?>">
                        </label>
                        
                        <label for="admin_email">
                            Email
                            <input type="email" id="admin_email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>">
                        </label>
                        
                        <div class="button-group">
                            <a href="install.php?step=1">Назад</a>
                            <button type="submit">Завершить установку</button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($step === '1' && !$success): ?>
                <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                    <h4>Перед установкой убедитесь, что:</h4>
                    <ul>
                        <li>Сервер MySQL запущен и доступен</li>
                        <li>У вас есть данные для подключения к БД (хост, пользователь, пароль)</li>
                        <li>Папка <code>config/</code> доступна для записи</li>
                        <li>Папка <code>uploads/</code> доступна для записи</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>