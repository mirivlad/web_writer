<?php
// views/books/index.php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2">Мои книги</h1>
            <p class="text-muted mb-0">Всего книг: <?= count($books) ?></p>
        </div>
        <div>
            <a href="<?= SITE_URL ?>/books/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Новая книга
            </a>
            <?php if (!empty($books)): ?>
                <button type="button" onclick="showDeleteAllConfirmation()" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Удалить все
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($books)): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-journal-bookmark fs-1 text-muted"></i>
            </div>
            <h3 class="h4 text-muted">У вас пока нет книг</h3>
            <p class="text-muted mb-4">Создайте свою первую книгу и начните писать!</p>
            <a href="<?= SITE_URL ?>/books/create" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle"></i> Создать первую книгу
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($books as $book): ?>
                <div class="col-xl-4 col-lg-6">
                    <div class="card h-100 book-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <?php if (!empty($book['cover_image'])): ?>
                                    <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                                         alt="<?= e($book['title']) ?>" 
                                         class="rounded me-3" 
                                         style="width: 60px; height: 80px; object-fit: cover;"
                                         onerror="this.style.display='none'">
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title mb-1">
                                            <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit" class="text-decoration-none">
                                                <?= e($book['title']) ?>
                                            </a>
                                        </h5>
                                        <span class="badge <?= $book['published'] ? 'bg-success' : 'bg-warning' ?>">
                                            <?= $book['published'] ? 'Опубликована' : 'Черновик' ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($book['genre'])): ?>
                                        <p class="text-muted small mb-2"><?= e($book['genre']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($book['description'])): ?>
                                <p class="card-text text-muted small">
                                    <?= e(mb_strimwidth($book['description'], 0, 120, '...')) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="border-end">
                                        <div class="fw-bold text-primary"><?= $book['chapter_count'] ?? 0 ?></div>
                                        <small class="text-muted">глав</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <div class="fw-bold text-success"><?= number_format($book['total_words'] ?? 0) ?></div>
                                        <small class="text-muted">слов</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div>
                                        <div class="fw-bold text-info">
                                            <?= $book['chapter_count'] > 0 ? number_format(($book['total_words'] ?? 0) / $book['chapter_count']) : 0 ?>
                                        </div>
                                        <small class="text-muted">слов/глава</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Редактировать
                                </a>
                                <div class="btn-group" role="group">
                                    <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-file-text"></i> Главы
                                    </a>
                                    <a href="<?= SITE_URL ?>/book/<?= $book['share_token'] ?>" class="btn btn-outline-success btn-sm" target="_blank">
                                        <i class="bi bi-eye"></i> Просмотр
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-4 p-3 bg-light rounded">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-2">
                    <strong class="text-primary"><?= count($books) ?></strong>
                    <small class="text-muted d-block">Книг</small>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <strong class="text-success"><?= array_sum(array_column($books, 'chapter_count')) ?></strong>
                    <small class="text-muted d-block">Глав</small>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <strong class="text-warning"><?= number_format(array_sum(array_column($books, 'total_words'))) ?></strong>
                    <small class="text-muted d-block">Слов</small>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <strong class="text-info"><?= count(array_filter($books, function($book) { return $book['published']; })) ?></strong>
                    <small class="text-muted d-block">Опубликовано</small>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($books)): ?>
<script>
function showDeleteAllConfirmation() {
    if (confirm('Вы уверены, что хотите удалить ВСЕ книги? Это действие также удалит все главы и обложки книг. Действие нельзя отменить!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= SITE_URL ?>/books/delete-all';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= generate_csrf_token() ?>';
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>