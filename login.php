<?php
require_once 'config/config.php';

// Если пользователь уже авторизован, перенаправляем на dashboard
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Ошибка безопасности";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Пожалуйста, введите имя пользователя и пароль';
        } else {
            $userModel = new User($pdo);
            $user = $userModel->findByUsername($username);

            if ($user && $userModel->verifyPassword($password, $user['password_hash'])) {
                if (!$user['is_active']) {
                    $error = 'Ваш аккаунт деактивирован или ожидает активации администратором.';
                } else {
                    // Успешный вход
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['display_name'] = $user['display_name'] ?: $user['username'];
                    $_SESSION['avatar'] = $user['avatar'] ?? null;
                    // Обновляем время последнего входа
                    $userModel->updateLastLogin($user['id']);
                    
                    $_SESSION['success'] = 'Добро пожаловать, ' . e($user['display_name'] ?: $user['username']) . '!';
                    redirect('dashboard.php');
                }
            } else {
                $error = 'Неверное имя пользователя или пароль';
            }
        }
    }
}

$page_title = 'Вход в систему';
include 'views/header.php';
?>

<div class="container">
    <h1>Вход в систему</h1>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= e($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <form method="post" style="max-width: 400px; margin: 0 auto;">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div style="margin-bottom: 1rem;">
            <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Имя пользователя
            </label>
            <input type="text" id="username" name="username" 
                   value="<?= e($_POST['username'] ?? '') ?>" 
                   placeholder="Введите имя пользователя" 
                   style="width: 100%;" 
                   required>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Пароль
            </label>
            <input type="password" id="password" name="password" 
                   placeholder="Введите пароль" 
                   style="width: 100%;" 
                   required>
        </div>

        <button type="submit" class="contrast" style="width: 100%;">
            🔑 Войти
        </button>
    </form>

    <div style="text-align: center; margin-top: 1rem;">
        <p>Нет аккаунта? <a href="register.php">Зарегистрируйтесь здесь</a></p>
    </div>
</div>

<?php include 'views/footer.php'; ?>