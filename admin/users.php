<?php
require_once '../config/config.php';
require_login();

// Проверяем права администратора (простая проверка - первый пользователь считается администратором)
if ($_SESSION['user_id'] != 1) {
    $_SESSION['error'] = "У вас нет доступа к этой странице";
    redirect('../dashboard.php');
}

$userModel = new User($pdo);
$users = $userModel->findAll();

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Ошибка безопасности";
    } else {
        $action = $_POST['action'] ?? '';
        $user_id = $_POST['user_id'] ?? null;
        
        if ($user_id && $user_id != $_SESSION['user_id']) { // Нельзя изменять себя
            switch ($action) {
                case 'toggle_active':
                    $user = $userModel->findById($user_id);
                    if ($user) {
                        $new_status = $user['is_active'] ? 0 : 1;
                        if ($userModel->updateStatus($user_id, $new_status)) {
                            $_SESSION['success'] = 'Статус пользователя обновлен';
                        } else {
                            $_SESSION['error'] = 'Ошибка при обновлении статуса';
                        }
                    }
                    break;
                    
                case 'delete':
                    if ($userModel->delete($user_id)) {
                        $_SESSION['success'] = 'Пользователь удален';
                    } else {
                        $_SESSION['error'] = 'Ошибка при удалении пользователя';
                    }
                    break;
            }
        } else {
            $_SESSION['error'] = 'Нельзя изменить собственный аккаунт';
        }
        
        redirect('users.php');
    }
}

$page_title = "Управление пользователями";
include '../views/header.php';
?>

<div class="container">
    <h1>Управление пользователями</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= e($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= e($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="margin: 0;">Всего пользователей: <?= count($users) ?></h2>
        <a href="../register.php" role="button">➕ Добавить пользователя</a>
    </div>

    <?php if (empty($users)): ?>
        <article style="text-align: center; padding: 2rem;">
            <h3>Пользователи не найдены</h3>
            <p>Зарегистрируйте первого пользователя</p>
            <a href="../register.php" role="button">📝 Добавить пользователя</a>
        </article>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 15%;">Имя пользователя</th>
                        <th style="width: 20%;">Отображаемое имя</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 15%;">Дата регистрации</th>
                        <th style="width: 10%;">Статус</th>
                        <th style="width: 15%;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td>
                            <strong><a href="/author.php?id=<?= $user['id'] ?>"><?= e($user['username']) ?></a></strong>
                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                <br><small style="color: #666;">(Вы)</small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($user['display_name']) ?></td>
                        <td><?= e($user['email']) ?></td>
                        <td>
                            <small><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></small>
                            <?php if ($user['last_login']): ?>
                                <br><small style="color: #666;">Вход: <?= date('d.m.Y H:i', strtotime($user['last_login'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="color: <?= $user['is_active'] ? 'green' : 'red' ?>">
                                <?= $user['is_active'] ? '✅ Активен' : '❌ Неактивен' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <div style="display: flex; gap: 3px; flex-wrap: wrap;">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="action" value="toggle_active">
                                    <button type="submit" class="compact-button secondary" title="<?= $user['is_active'] ? 'Деактивировать' : 'Активировать' ?>">
                                        <?= $user['is_active'] ? '⏸️' : '▶️' ?>
                                    </button>
                                </form>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить пользователя «<?= e($user['username']) ?>»? Все его книги и главы также будут удалены.');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="compact-button secondary" style="background: #ff4444; border-color: #ff4444; color: white;" title="Удалить">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                            <?php else: ?>
                                <small style="color: #666;">Текущий пользователь</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../views/footer.php'; ?>