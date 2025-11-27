<?php include 'views/layouts/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Добавление пользователя</h1>
        <a href="<?= SITE_URL ?>/admin/users" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к пользователям
        </a>
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

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Имя пользователя *</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= e($_POST['username'] ?? '') ?>" 
                                           placeholder="Введите имя пользователя" 
                                           required
                                           pattern="[a-zA-Z0-9_]+"
                                           title="Только латинские буквы, цифры и символ подчеркивания">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Отображаемое имя</label>
                                    <input type="text" class="form-control" id="display_name" name="display_name" 
                                           value="<?= e($_POST['display_name'] ?? '') ?>" 
                                           placeholder="Введите отображаемое имя">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= e($_POST['email'] ?? '') ?>" 
                                   placeholder="Введите email">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Пароль *</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Введите пароль (минимум 6 символов)" 
                                           required
                                           minlength="6">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirm" class="form-label">Подтверждение пароля *</label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                           placeholder="Повторите пароль" 
                                           required
                                           minlength="6">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                    <?= isset($_POST['is_active']) ? 'checked' : 'checked' ?>>
                                <label class="form-check-label" for="is_active">
                                    Активировать пользователя сразу
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Добавить пользователя
                            </button>
                            <a href="<?= SITE_URL ?>/admin/users" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle"></i> Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>