<?php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Мои серии книг</h1>
        <a href="/series/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать серию
        </a>
    </div>

    <?php if (empty($series)): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-collection fs-1 text-muted"></i>
            </div>
            <h3 class="h4 text-muted">Пока нет серий</h3>
            <p class="text-muted mb-4">
                Создайте свою первую серию, чтобы организовать книги в циклы и сериалы.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="/series/create" class="btn btn-primary">Создать серию</a>
                <a href="/books" class="btn btn-outline-secondary">Перейти к книгам</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($series as $ser): ?>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title">
                                    <a href="/series/<?= $ser['id'] ?>/edit" class="text-decoration-none"><?= e($ser['title']) ?></a>
                                </h5>
                                <span class="badge bg-primary"><?= $ser['book_count'] ?? 0 ?> книг</span>
                            </div>
                            
                            <div class="text-muted small mb-3">
                                Создана <?= date('d.m.Y', strtotime($ser['created_at'])) ?>
                                <?php if ($ser['updated_at'] != $ser['created_at']): ?>
                                    • Обновлена <?= date('d.m.Y', strtotime($ser['updated_at'])) ?>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($ser['description'])): ?>
                                <p class="card-text"><?= e($ser['description']) ?></p>
                            <?php endif; ?>

                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="border-end">
                                        <div class="fw-bold text-primary"><?= $ser['book_count'] ?? 0 ?></div>
                                        <small class="text-muted">книг</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <div class="fw-bold text-success"><?= number_format($ser['total_words'] ?? 0) ?></div>
                                        <small class="text-muted">слов</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div>
                                        <div class="fw-bold text-info">
                                            <?= $ser['book_count'] > 0 ? number_format(round($ser['total_words'] / $ser['book_count'])) : 0 ?>
                                        </div>
                                        <small class="text-muted">слов/книга</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="/series/<?= $ser['id'] ?>/edit" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Управление
                                </a>
                                <a href="/series/<?= $ser['id'] ?>/view" class="btn btn-outline-success btn-sm" target="_blank">
                                    <i class="bi bi-eye"></i> Публично
                                </a>
                                <form method="post" action="/series/<?= $ser['id'] ?>/delete" 
                                      onsubmit="return confirm('Удалить серию? Книги останутся, но будут удалены из серии.')">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i> Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
include 'views/layouts/footer.php';
?>