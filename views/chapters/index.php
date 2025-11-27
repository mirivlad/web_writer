<?php
// views/chapters/index.php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2">Главы книги: <?= e($book['title']) ?></h1>
            <p class="text-muted mb-0">Управление главами вашей книги</p>
        </div>
        <div class="btn-group">
            <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Новая глава
            </a>
            <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Редактировать книгу
            </a>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="<?= SITE_URL ?>/book/<?= $book['share_token'] ?>" class="btn btn-outline-success" target="_blank">
            <i class="bi bi-eye"></i> Публичный доступ
        </a>
        <a href="<?= SITE_URL ?>/book/all/<?= $book['id'] ?>" class="btn btn-outline-info" target="_blank">
            <i class="bi bi-list-ul"></i> Полный обзор
        </a>
    </div>

    <?php if (empty($chapters)): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-file-text fs-1 text-muted"></i>
            </div>
            <h3 class="h4 text-muted">В этой книге пока нет глав</h3>
            <p class="text-muted mb-4">Создайте первую главу для вашей книги</p>
            <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Создать первую главу
            </a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%;">№</th>
                                <th style="width: 40%;">Название главы</th>
                                <th style="width: 15%;">Статус</th>
                                <th style="width: 10%;">Слов</th>
                                <th style="width: 20%;">Обновлено</th>
                                <th style="width: 10%;">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chapters as $index => $chapter): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= e($chapter['title']) ?></strong>
                                    <?php if ($chapter['content']): ?>
                                        <br><small class="text-muted"><?= e(mb_strimwidth($chapter['content'], 0, 100, '...')) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $chapter['status'] == 'published' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $chapter['status'] == 'published' ? 'Опубликована' : 'Черновик' ?>
                                    </span>
                                </td>
                                <td><?= $chapter['word_count'] ?></td>
                                <td>
                                    <small><?= date('d.m.Y H:i', strtotime($chapter['updated_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= SITE_URL ?>/chapters/<?= $chapter['id'] ?>/edit" class="btn btn-outline-primary" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="post" action="<?= SITE_URL ?>/chapters/<?= $chapter['id'] ?>/delete" 
                                              onsubmit="return confirm('Вы уверены, что хотите удалить эту главу? Это действие нельзя отменить.');">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Удалить">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3 p-3 bg-light rounded">
            <div class="row text-center">
                <div class="col-md-4 col-6 mb-2">
                    <strong class="text-primary"><?= count($chapters) ?></strong>
                    <small class="text-muted d-block">Всего глав</small>
                </div>
                <div class="col-md-4 col-6 mb-2">
                    <strong class="text-success"><?= array_sum(array_column($chapters, 'word_count')) ?></strong>
                    <small class="text-muted d-block">Всего слов</small>
                </div>
                <div class="col-md-4 col-12 mb-2">
                    <strong class="text-info"><?= count(array_filter($chapters, function($ch) { return $ch['status'] == 'published'; })) ?></strong>
                    <small class="text-muted d-block">Опубликовано</small>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'views/layouts/footer.php'; ?>