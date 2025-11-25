<?php include 'views/layouts/header.php'; ?>

<div class="container">
    <h1>Добавление пользователя</h1>
    
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
        <div style="margin-bottom: 1rem;">
            <label for="password_confirm" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Подтверждение пароля *
            </label>
            <input type="password" id="password_confirm" name="password_confirm" 
                   placeholder="Повторите пароль" 
                   style="width: 100%;" 
                   required
                   minlength="6">
        </div>
        <div style="margin-bottom: 1.5rem;">
            <label for="is_active">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                <?= isset($_POST['is_active']) ? 'checked' : 'checked' ?>>
                Активировать пользователя сразу
            </label>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="contrast" style="flex: 1;">
                👥 Добавить пользователя
            </button>
            <a href="<?= SITE_URL ?>/admin/users" class="secondary" style="display: flex; align-items: center; justify-content: center; padding: 0.75rem; text-decoration: none;">
                ❌ Отмена
            </a>
        </div>
    </form>
</div>

<?php include 'views/layouts/footer.php'; ?>