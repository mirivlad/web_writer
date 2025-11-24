<?php
require_once 'config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$userModel = new User($pdo);
$user = $userModel->findById($user_id);

$message = '';
$avatar_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Ошибка безопасности";
    } else {
        $display_name = trim($_POST['display_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        // Обработка загрузки аватарки
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar_result = handleAvatarUpload($_FILES['avatar'], $user_id);
            if ($avatar_result['success']) {
                $userModel->updateAvatar($user_id, $avatar_result['filename']);
                // Обновляем данные пользователя
                $user = $userModel->findById($user_id);
            } else {
                $avatar_error = $avatar_result['error'];
            }
        }
        
        // Обработка удаления аватарки
        if (isset($_POST['delete_avatar']) && $_POST['delete_avatar'] == '1') {
            deleteUserAvatar($user_id);
            $user = $userModel->findById($user_id);
        }
        
        // Обновляем основные данные
        $data = [
            'display_name' => $display_name,
            'email' => $email,
            'bio' => $bio
        ];
        
        if ($userModel->updateProfile($user_id, $data)) {
            $_SESSION['display_name'] = $display_name ?: $user['username'];
            $message = "Профиль обновлен";
            // Обновляем данные пользователя
            $user = $userModel->findById($user_id);
        } else {
            $message = "Ошибка при обновлении профиля";
        }
    }
}

$page_title = "Мой профиль";
include 'views/header.php';
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
                <small style="color: #666;">
                    Поддерживается Markdown форматирование
                </small>
            </div>
            
            <div class="profile-buttons">
                <button type="submit" class="profile-button primary">
                    💾 Сохранить изменения
                </button>
                <a href="dashboard.php" class="profile-button secondary">
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
                     style="max-width: 200px; height: auto; border-radius: 50%; border: 3px solid #007bff;"
                     onerror="this.style.display='none'">
            <?php else: ?>
                <div style="width: 200px; height: 200px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem; margin: 0 auto;">
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
                <small style="color: #666;">
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
            <div style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                <p style="margin: 0; font-size: 0.9em; color: #666;">
                    <strong>Примечание:</strong> Аватарка отображается на вашей публичной странице автора
                </p>
            </div>
        <?php endif; ?>
    </article>
</div>

<article>
    <h3>Информация об аккаунте</h3>
    <p><a href="author.php?id=<?= $_SESSION['user_id'] ?>" target="_blank" class="adaptive-button secondary">
        👁️ Посмотреть мою публичную страницу
    </a></p>
    <p><strong>Дата регистрации:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></p>
    <?php if ($user['last_login']): ?>
        <p><strong>Последний вход:</strong> <?= date('d.m.Y H:i', strtotime($user['last_login'])) ?></p>
    <?php endif; ?>
</article>

<?php include 'views/footer.php'; ?>