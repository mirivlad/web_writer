<?php
// views/user/view_public.php
include 'views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <article class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <header class="text-center mb-5">
                        <!-- Аватарка автора -->
                        <div class="mb-4">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?= AVATARS_URL . e($user['avatar']) ?>" 
                                     alt="<?= e($user['display_name'] ?: $user['username']) ?>" 
                                     class="rounded-circle border border-4 border-primary"
                                     style="width: 150px; height: 150px; object-fit: cover;"
                                     onerror="this.style.display='none'">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary bg-gradient d-flex align-items-center justify-content-center mx-auto"
                                     style="width: 150px; height: 150px;">
                                    <span class="text-white fw-bold display-6">
                                        <?= mb_substr(e($user['display_name'] ?: $user['username']), 0, 1) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h1 class="display-5 mb-2"><?= e($user['display_name'] ?: $user['username']) ?></h1>
                        
                        <!-- Биография автора -->
                        <?php if (!empty($user['bio'])): ?>
                            <div class="bg-light p-4 rounded mb-4">
                                <?= e($user['bio']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Статистика автора -->
                        <div class="d-flex justify-content-center gap-4 flex-wrap">
                            <div class="text-center">
                                <div class="h3 text-primary mb-0"><?= $total_books ?></div>
                                <small class="text-muted">Книг</small>
                            </div>
                            <div class="text-center">
                                <div class="h3 text-success mb-0"><?= $total_chapters ?></div>
                                <small class="text-muted">Глав</small>
                            </div>
                            <div class="text-center">
                                <div class="h3 text-warning mb-0"><?= $total_words ?></div>
                                <small class="text-muted">Слов</small>
                            </div>
                        </div>
                    </header>

                    <h2 class="h3 text-center mb-4">Публикации автора</h2>

                    <?php if (empty($books)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-journal-bookmark fs-1 text-muted"></i>
                            <h3 class="h4 text-muted mt-3">У этого автора пока нет опубликованных книг</h3>
                            <p class="text-muted">Следите за обновлениями, скоро здесь появятся новые произведения!</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($books as $book): ?>
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start mb-3">
                                                <?php if ($book['cover_image']): ?>
                                                    <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                                                         alt="<?= e($book['title']) ?>" 
                                                         class="rounded me-3"
                                                         style="width: 60px; height: 80px; object-fit: cover;"
                                                         onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <div class="bg-primary bg-gradient rounded d-flex align-items-center justify-content-center me-3"
                                                         style="width: 60px; height: 80px;">
                                                        <i class="bi bi-book text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title"><?= e($book['title']) ?></h5>
                                                    <?php if ($book['genre']): ?>
                                                        <p class="text-muted small mb-1"><em><?= e($book['genre']) ?></em></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <?php if ($book['description']): ?>
                                                <p class="card-text text-muted small mb-3">
                                                    <?= nl2br(e($book['description'])) ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php
                                            $book_stats = $book_model->getBookStats($book['id'], true);
                                            $chapter_count = $book_stats['chapter_count'] ?? 0;
                                            $word_count = $book_stats['total_words'] ?? 0;
                                            ?>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <a href="<?= SITE_URL ?>/book/<?= e($book['share_token']) ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-book"></i> Читать книгу
                                                </a>
                                                <small class="text-muted">
                                                    Глав: <?= $chapter_count ?> | Слов: <?= $word_count ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <footer class="text-center mt-5 pt-4 border-top">
                        <p class="text-muted">
                            Страница автора создана в <?= e(APP_NAME) ?> • <?= date('Y') ?>
                        </p>
                    </footer>
                </div>
            </article>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
</style>

<?php include 'views/layouts/footer.php'; ?>