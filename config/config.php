<?php
// config/config.php
// Подключаем функции
require_once __DIR__ . '/../includes/functions.php';
session_start();

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_USER', 'writer_mirv');
define('DB_PASS', 'writer_moloko22'); 
define('DB_NAME', 'writer_app');
define('SITE_URL', 'https://writer.mirv.top');
//define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/'); // Измените на ваш домен
// Настройки приложения
define('APP_NAME', 'Web Writer');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('COVERS_PATH', UPLOAD_PATH . 'covers/');
define('COVERS_URL', SITE_URL . '/uploads/covers/');

// Создаем папку для загрузок, если ее нет
// if (!file_exists(COVERS_PATH)) {
//     mkdir(COVERS_PATH, 0765, true);
// }

// Подключение к базе данных
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    die("Ошибка подключения к базе данных");
}


// Автозагрузка моделей
spl_autoload_register(function ($class_name) {
    $model_file = __DIR__ . '/../models/' . $class_name . '.php';
    if (file_exists($model_file)) {
        require_once $model_file;
    }
});
?>