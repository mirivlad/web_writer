// views/auth/register.php
<?php
// views/auth/register.php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus fs-1 text-primary"></i>
                        <h1 class="h3 mt-2">Регистрация</h1>
                    </div>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger">
                            <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success">
                            <?= e($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Имя пользователя *</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= e($_POST['username'] ?? '') ?>" 
                                           placeholder="Введите имя пользователя" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Отображаемое имя</label>
                                    <input type="text" class="form-control" id="display_name" name="display_name" 
                                           value="<?= e($_POST['display_name'] ?? '') ?>" 
                                           placeholder="Как вас будут видеть другие">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= e($_POST['email'] ?? '') ?>" 
                                   placeholder="email@example.com">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Пароль *</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Не менее 6 символов" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="password_confirm" class="form-label">Подтверждение пароля *</label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                           placeholder="Повторите пароль" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-person-plus"></i> Зарегистрироваться
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted">Уже есть аккаунт? 
                            <a href="<?= SITE_URL ?>/login" class="text-decoration-none">Войдите здесь</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>