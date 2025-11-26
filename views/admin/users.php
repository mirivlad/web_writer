<?php include 'views/layouts/header.php'; ?>

<div class="container" style="margin:0; width: auto;">
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
        <a href="<?= SITE_URL ?>/admin/add-user" class="action-button primary">➕ Добавить пользователя</a>
    </div>
    
    <?php if (empty($users)): ?>
        <article style="text-align: center; padding: 2rem;">
            <h3>Пользователи не найдены</h3>
            <p>Зарегистрируйте первого пользователя</p>
            <a href="<?= SITE_URL ?>/admin/add-user" role="button">📝 Добавить пользователя</a>
        </article>
    <?php else: ?>
        <div style="overflow-x: auto; width:100%;">
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
                            <strong><a href="<?= SITE_URL ?>/author/<?= $user['id'] ?>"><?= e($user['username']) ?></a></strong>
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
                                <form method="post" action="<?= SITE_URL ?>/admin/user/<?= $user['id'] ?>/toggle-status" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <button type="submit" class="compact-button secondary" title="<?= $user['is_active'] ? 'Деактивировать' : 'Активировать' ?>">
                                        <?= $user['is_active'] ? '⏸️' : '▶️' ?>
                                    </button>
                                </form>
                                <form method="post" action="<?= SITE_URL ?>/admin/user/<?= $user['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить пользователя «<?= e($user['username']) ?>»? Все его книги и главы также будут удалены.');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
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

<?php include 'views/layouts/footer.php'; ?>