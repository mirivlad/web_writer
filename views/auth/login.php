<?php
// views/auth/login.php
include 'views/layouts/header.php';
?>

<div class="container">
    <h1>Вход в систему</h1>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error">
            <?= e($error) ?>
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
        <p>Нет аккаунта? <a href="<?= SITE_URL ?>/register">Зарегистрируйтесь здесь</a></p>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>