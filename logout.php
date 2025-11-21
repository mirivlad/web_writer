<?php
require_once 'config/config.php';

// Очищаем все данные сессии
$_SESSION = [];


if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Редирект на страницу входа
redirect('login.php');
?>