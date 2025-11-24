<?php
// views/auth/register.php
include 'views/layouts/header.php';
?>

<div class="container">
    <h1>Регистрация</h1>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <form method="post" style="max-width: 400px; margin: 0 auto;">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div style="margin-bottom: 1rem;">
            <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Имя пользователя *
            </label>
            <input type="text" id="username" name="username" 
                   value="<?= e($_POST['username'] ?? '') ?>" 
                   placeholder="Введите имя пользователя" 
                   style="width: 100%;" 
                   required>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="display_name" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Отображаемое имя
            </label>
            <input type="text" id="display_name" name="display_name" 
                   value="<?= e($_POST['display_name'] ?? '') ?>" 
                   placeholder="Как вас будут видеть другие" 
                   style="width: 100%;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Email
            </label>
            <input type="email" id="email" name="email" 
                   value="<?= e($_POST['email'] ?? '') ?>" 
                   placeholder="email@example.com" 
                   style="width: 100%;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Пароль *
            </label>
            <input type="password" id="password" name="password" 
                   placeholder="Не менее 6 символов" 
                   style="width: 100%;" 
                   required>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label for="password_confirm" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Подтверждение пароля *
            </label>
            <input type="password" id="password_confirm" name="password_confirm" 
                   placeholder="Повторите пароль" 
                   style="width: 100%;" 
                   required>
        </div>

        <button type="submit" class="contrast" style="width: 100%;">
            📝 Зарегистрироваться
        </button>
    </form>

    <div style="text-align: center; margin-top: 1rem;">
        <p>Уже есть аккаунт? <a href="<?= SITE_URL ?>/login">Войдите здесь</a></p>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>