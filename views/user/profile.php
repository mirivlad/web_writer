<?php
// views/user/profile.php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Мой профиль</h1>
        <a href="<?= SITE_URL ?>/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад в панель
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert <?= strpos($message, 'Ошибка') !== false ? 'alert-danger' : 'alert-success' ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Основная информация</h5>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Имя пользователя (нельзя изменить)</label>
                            <input type="text" class="form-control" id="username" value="<?= e($user['username']) ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="display_name" class="form-label">Отображаемое имя *</label>
                            <input type="text" class="form-control" id="display_name" name="display_name" 
                                   value="<?= e($user['display_name'] ?? $user['username']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= e($user['email'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="bio" class="form-label">О себе</label>
                            <textarea class="form-control" id="bio" name="bio" 
                                      placeholder="Расскажите о себе, своих интересах, стиле письма..."
                                      rows="6"><?= e($user['bio'] ?? '') ?></textarea>
                            <div class="form-text">
                                Отображается на вашей публичной странице. Поддерживается Markdown форматирование.
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Сохранить изменения
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Аватарка</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= AVATARS_URL . e($user['avatar']) ?>" 
                             alt="Аватарка" 
                             class="rounded-circle mb-3 border border-3 border-primary"
                             style="width: 150px; height: 150px; object-fit: cover;"
                             onerror="this.style.display='none'">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary bg-gradient d-flex align-items-center justify-content-center mx-auto mb-3"
                             style="width: 150px; height: 150px;">
                            <span class="text-white fw-bold fs-2">
                                <?= mb_substr(e($user['display_name'] ?? $user['username']), 0, 1) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="avatar" class="form-label">Загрузить новую аватарку</label>
                            <input type="file" class="form-control" id="avatar" name="avatar" 
                                   accept="image/jpeg, image/png, image/gif, image/webp">
                            <div class="form-text">
                                Разрешены: JPG, PNG, GIF, WebP. Максимальный размер: 2MB.
                            </div>
                            
                            <?php if (!empty($avatar_error)): ?>
                                <div class="alert alert-danger mt-2">
                                    <i class="bi bi-exclamation-triangle"></i> <?= e($avatar_error) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Загрузить аватарку
                            </button>
                            
                            <?php if (!empty($user['avatar'])): ?>
                                <button type="submit" name="delete_avatar" value="1" class="btn btn-outline-danger">
                                    <i class="bi bi-trash"></i> Удалить аватарку
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Информация об аккаунте</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="<?= SITE_URL ?>/author/<?= $_SESSION['user_id'] ?>" target="_blank" class="btn btn-outline-primary w-100">
                            <i class="bi bi-eye"></i> Посмотреть публичную страницу
                        </a>
                    </div>
                    <p><strong>Дата регистрации:</strong><br>
                    <small class="text-muted"><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></small></p>
                    <?php if ($user['last_login']): ?>
                        <p><strong>Последний вход:</strong><br>
                        <small class="text-muted"><?= date('d.m.Y H:i', strtotime($user['last_login'])) ?></small></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>