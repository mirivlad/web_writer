<?php
require_once 'config/config.php';

// Если пользователь уже авторизован И он не администратор (ID != 1), перенаправляем на dashboard
if (is_logged_in() && $_SESSION['user_id'] != 1) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

// Проверяем, является ли текущий пользователь администратором
$is_admin = is_logged_in() && $_SESSION['user_id'] == 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Ошибка безопасности";
    } else {
        $username = trim($_POST['username'] ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Валидация
        if (empty($username) || empty($password)) {
            $error = 'Имя пользователя и пароль обязательны для заполнения';
        } elseif ($password !== $confirm_password) {
            $error = 'Пароли не совпадают';
        } elseif (strlen($password) < 6) {
            $error = 'Пароль должен содержать не менее 6 символов';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error = 'Имя пользователя может содержать только латинские буквы, цифры и символ подчеркивания';
        } else {
            $userModel = new User($pdo);

            // Проверяем, не занят ли username
            if ($userModel->findByUsername($username)) {
                $error = 'Это имя пользователя уже занято';
            } elseif (!empty($email) && $userModel->findByEmail($email)) {
                $error = 'Этот email уже используется';
            } else {
                // Подготавливаем данные для создания пользователя
                $user_data = [
                    'username' => $username,
                    'display_name' => $display_name ?: $username,
                    'email' => $email,
                    'password' => $password
                ];
                
                // Если пользователя создает администратор - сразу активный
                // Если пользователь регистрируется сам - неактивный (требует активации)
                if ($is_admin) {
                    $user_data['is_active'] = 1;
                } else {
                    $user_data['is_active'] = 0;
                }
                
                // Создаем пользователя
                $success = $userModel->create($user_data);

                if ($success) {
                    if ($is_admin) {
                        $_SESSION['success'] = 'Пользователь успешно создан и активирован';
                        redirect('admin/users.php');
                    } else {
                        $_SESSION['success'] = 'Регистрация прошла успешно. Ваш аккаунт ожидает активации администратором.';
                        redirect('login.php');
                    }
                } else {
                    $error = 'Произошла ошибка при регистрации. Попробуйте еще раз.';
                }
            }
        }
    }
}

$page_title = $is_admin ? 'Добавление пользователя' : 'Регистрация';
include 'views/header.php';
?>

<div class="container">
    <h1><?= $is_admin ? 'Добавление пользователя' : 'Регистрация' ?></h1>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <form method="post" style="max-width: 500px; margin: 0 auto;">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div style="margin-bottom: 1rem;">
            <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Имя пользователя *
            </label>
            <input type="text" id="username" name="username" 
                   value="<?= e($_POST['username'] ?? '') ?>" 
                   placeholder="Введите имя пользователя" 
                   style="width: 100%;" 
                   required
                   pattern="[a-zA-Z0-9_]+"
                   title="Только латинские буквы, цифры и символ подчеркивания">
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="display_name" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Отображаемое имя
            </label>
            <input type="text" id="display_name" name="display_name" 
                   value="<?= e($_POST['display_name'] ?? '') ?>" 
                   placeholder="Введите отображаемое имя" 
                   style="width: 100%;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Email
            </label>
            <input type="email" id="email" name="email" 
                   value="<?= e($_POST['email'] ?? '') ?>" 
                   placeholder="Введите email" 
                   style="width: 100%;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Пароль *
            </label>
            <input type="password" id="password" name="password" 
                   placeholder="Введите пароль (минимум 6 символов)" 
                   style="width: 100%;" 
                   required
                   minlength="6">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Подтверждение пароля *
            </label>
            <input type="password" id="confirm_password" name="confirm_password" 
                   placeholder="Повторите пароль" 
                   style="width: 100%;" 
                   required
                   minlength="6">
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="contrast" style="flex: 1;">
                <?= $is_admin ? '👥 Добавить пользователя' : '📝 Зарегистрироваться' ?>
            </button>
            
            <?php if ($is_admin): ?>
                <a href="admin/users.php" class="secondary" style="display: flex; align-items: center; justify-content: center; padding: 0.75rem; text-decoration: none;">
                    ❌ Отмена
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (!$is_admin): ?>
        <div style="text-align: center; margin-top: 1rem;">
            <p>Уже есть аккаунт? <a href="login.php">Войдите здесь</a></p>
            <?php if (!$is_admin): ?>
                <p style="color: #666; font-size: 0.9em; margin-top: 0.5rem;">
                    <strong>Примечание:</strong> После регистрации ваш аккаунт должен быть активирован администратором.
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'views/footer.php'; ?>