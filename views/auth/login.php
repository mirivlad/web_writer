<?php
// views/auth/login.php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-check fs-1 text-primary"></i>
                        <h1 class="h3 mt-2">Вход в систему</h1>
                    </div>

                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger">
                            <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Имя пользователя</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= e($_POST['username'] ?? '') ?>" 
                                   placeholder="Введите имя пользователя" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Введите пароль" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-box-arrow-in-right"></i> Войти
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted">Нет аккаунта? 
                            <a href="<?= SITE_URL ?>/register" class="text-decoration-none">Зарегистрируйтесь здесь</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>