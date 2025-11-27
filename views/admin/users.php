<?php include 'views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2">Управление пользователями</h1>
            <p class="text-muted mb-0">Всего пользователей: <?= count($users) ?></p>
        </div>
        <a href="<?= SITE_URL ?>/admin/add-user" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Добавить пользователя
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= e($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= e($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-people fs-1 text-muted"></i>
            </div>
            <h3 class="h4 text-muted">Пользователи не найдены</h3>
            <p class="text-muted mb-4">Зарегистрируйте первого пользователя</p>
            <a href="<?= SITE_URL ?>/admin/add-user" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Добавить пользователя
            </a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя пользователя</th>
                                <th>Отображаемое имя</th>
                                <th>Email</th>
                                <th>Дата регистрации</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <strong>
                                        <a href="<?= SITE_URL ?>/author/<?= $user['id'] ?>" class="text-decoration-none">
                                            <?= e($user['username']) ?>
                                        </a>
                                    </strong>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <br><small class="text-muted">(Вы)</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($user['display_name']) ?></td>
                                <td><?= e($user['email']) ?></td>
                                <td>
                                    <small><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></small>
                                    <?php if ($user['last_login']): ?>
                                        <br><small class="text-muted">Вход: <?= date('d.m.Y H:i', strtotime($user['last_login'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $user['is_active'] ? 'Активен' : 'Неактивен' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <div class="btn-group btn-group-sm">
                                        <form method="post" action="<?= SITE_URL ?>/admin/user/<?= $user['id'] ?>/toggle-status" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <button type="submit" class="btn btn-outline-<?= $user['is_active'] ? 'warning' : 'success' ?>" 
                                                    title="<?= $user['is_active'] ? 'Деактивировать' : 'Активировать' ?>">
                                                <i class="bi bi-<?= $user['is_active'] ? 'pause' : 'play' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="post" action="<?= SITE_URL ?>/admin/user/<?= $user['id'] ?>/delete" 
                                              onsubmit="return confirm('Вы уверены, что хотите удалить пользователя «<?= e($user['username']) ?>»? Все его книги и главы также будут удалены.');"
                                              class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Удалить">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <?php else: ?>
                                        <small class="text-muted">Текущий пользователь</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'views/layouts/footer.php'; ?>