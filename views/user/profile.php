<?php
// views/user/profile.php
include 'views/layouts/header.php';
?>

<h1>Мой профиль</h1>

<?php if ($message): ?>
    <div class="alert <?= strpos($message, 'Ошибка') !== false ? 'alert-error' : 'alert-success' ?>">
        <?= e($message) ?>
    </div>
<?php endif; ?>

<div class="grid">
    <article>
        <h2>Основная информация</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div style="margin-bottom: 1rem;">
                <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                    Имя пользователя (нельзя изменить)
                </label>
                <input type="text" id="username" value="<?= e($user['username']) ?>" disabled style="width: 100%;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label for="display_name" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                    Отображаемое имя *
                </label>
                <input type="text" id="display_name" name="display_name" 
                       value="<?= e($user['display_name'] ?? $user['username']) ?>" 
                       style="width: 100%;" required>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                    Email
                </label>
                <input type="email" id="email" name="email" 
                       value="<?= e($user['email'] ?? '') ?>" 
                       style="width: 100%;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="bio" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                    О себе (отображается на вашей публичной странице)
                </label>
                <textarea id="bio" name="bio" 
                          placeholder="Расскажите о себе, своих интересах, стиле письма..."
                          rows="6"
                          style="width: 100%;"><?= e($user['bio'] ?? '') ?></textarea>
                <small style="color: var(--muted-color);">
                    Поддерживается Markdown форматирование
                </small>
            </div>
            
            <div class="profile-buttons">
                <button type="submit" class="profile-button primary">
                    💾 Сохранить изменения
                </button>
                <a href="<?= SITE_URL ?>/dashboard" class="profile-button secondary">
                    ↩️ Назад
                </a>
            </div>
        </form>
    </article>
    
    <article>
        <h2>Аватарка</h2>
        
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= AVATARS_URL . e($user['avatar']) ?>" 
                     alt="Аватарка" 
                     style="max-width: 200px; height: auto; border-radius: 50%; border: 3px solid var(--primary);"
                     onerror="this.style.display='none'">
            <?php else: ?>
                <div style="width: 200px; height: 200px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem; margin: 0 auto;">
                    <?= mb_substr(e($user['display_name'] ?? $user['username']), 0, 1) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div style="margin-bottom: 1rem;">
                <label for="avatar" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                    Загрузить новую аватарку
                </label>
                <input type="file" id="avatar" name="avatar" 
                       accept="image/jpeg, image/png, image/gif, image/webp"
                       style="height: 2.6rem;">
                <small style="color: var(--muted-color);">
                    Разрешены: JPG, PNG, GIF, WebP. Максимальный размер: 2MB.
                    Рекомендуемый размер: 200×200 пикселей.
                </small>
                
                <?php if (!empty($avatar_error)): ?>
                    <div style="color: #d32f2f; margin-top: 0.5rem;">
                        ❌ <?= e($avatar_error) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="contrast" style="flex: 1;">
                    📤 Загрузить аватарку
                </button>
                
                <?php if (!empty($user['avatar'])): ?>
                    <button type="submit" name="delete_avatar" value="1" class="secondary" style="flex: 1; background: #ff4444; border-color: #ff4444; color: white;">
                        🗑️ Удалить аватарку
                    </button>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if (!empty($user['avatar'])): ?>
            <div style="margin-top: 1rem; padding: 1rem; background: var(--card-background-color); border-radius: 5px;">
                <p style="margin: 0; font-size: 0.9em; color: var(--muted-color);">
                    <strong>Примечание:</strong> Аватарка отображается на вашей публичной странице автора
                </p>
            </div>
        <?php endif; ?>
    </article>
</div>

<article>
    <h3>Информация об аккаунте</h3>
    <p><a href="<?= SITE_URL ?>/author/<?= $_SESSION['user_id'] ?>" target="_blank" class="adaptive-button secondary">
        👁️ Посмотреть мою публичную страницу
    </a></p>
    <p><strong>Дата регистрации:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></p>
    <?php if ($user['last_login']): ?>
        <p><strong>Последний вход:</strong> <?= date('d.m.Y H:i', strtotime($user['last_login'])) ?></p>
    <?php endif; ?>
</article>

<?php include 'views/layouts/footer.php'; ?>