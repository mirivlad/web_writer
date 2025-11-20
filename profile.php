<?php
require_once 'config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$userModel = new User($pdo);
$user = $userModel->findById($user_id);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = "Ошибка безопасности";
    } else {
        $display_name = trim($_POST['display_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $stmt = $pdo->prepare("UPDATE users SET display_name = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$display_name, $email, $user_id])) {
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

<article>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div style="margin-bottom: 1rem;">
            <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Имя пользователя (нельзя изменить)
            </label>
            <input type="text" id="username" value="<?= e($user['username']) ?>" disabled style="width: 100%;">
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label for="display_name" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Отображаемое имя
            </label>
            <input type="text" id="display_name" name="display_name" 
                   value="<?= e($user['display_name'] ?? $user['username']) ?>" style="width: 100%;">
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Email
            </label>
            <input type="email" id="email" name="email" value="<?= e($user['email'] ?? '') ?>" style="width: 100%;">
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
    <h3>Информация об аккаунте</h3>
    <p><a href="author.php?id=<?= $_SESSION['user_id'] ?>" target="_blank">Моя публичная страница</a></p>
    <p><strong>Дата регистрации:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></p>
    <?php if ($user['last_login']): ?>
        <p><strong>Последний вход:</strong> <?= date('d.m.Y H:i', strtotime($user['last_login'])) ?></p>
    <?php endif; ?>
</article>

<?php include 'views/footer.php'; ?>