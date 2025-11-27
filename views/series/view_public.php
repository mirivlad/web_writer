<?php
// views/series/view_public.php
include 'views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <article class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <header class="text-center mb-5">
                        <h1 class="display-5 mb-3"><?= e($series['title']) ?></h1>
                        
                        <p class="lead text-muted mb-3">
                            Серия книг от 
                            <a href="<?= SITE_URL ?>/author/<?= $author['id'] ?>" class="text-decoration-none">
                                <?= e($author['display_name'] ?: $author['username']) ?>
                            </a>
                        </p>
                        
                        <?php if ($series['description']): ?>
                            <div class="bg-light p-4 rounded mb-4">
                                <?= e($series['description']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-center gap-4 flex-wrap">
                            <div class="text-center">
                                <div class="h4 text-primary mb-0"><?= count($books) ?></div>
                                <small class="text-muted">Книг</small>
                            </div>
                            <div class="text-center">
                                <div class="h4 text-success mb-0"><?= $total_chapters ?></div>
                                <small class="text-muted">Глав</small>
                            </div>
                            <div class="text-center">
                                <div class="h4 text-warning mb-0"><?= $total_words ?></div>
                                <small class="text-muted">Слов</small>
                            </div>
                        </div>
                    </header>

                    <?php if (empty($books)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-collection fs-1 text-muted"></i>
                            <h3 class="h4 text-muted mt-3">В этой серии пока нет опубликованных книг</h3>
                            <p class="text-muted">Автор еще не опубликовал книги из этой серии</p>
                        </div>
                    <?php else: ?>
                        <div class="mb-5">
                            <h2 class="h3 text-center mb-4">
                                <i class="bi bi-book"></i> Книги серии
                            </h2>
                            
                            <?php foreach ($books as $book): ?>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row align-items-start">
                                        <?php if ($book['cover_image']): ?>
                                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                                <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                                                     alt="<?= e($book['title']) ?>" 
                                                     class="img-fluid rounded shadow"
                                                     style="max-height: 200px;"
                                                     onerror="this.style.display='none'">
                                            </div>
                                            <div class="col-md-9">
                                        <?php else: ?>
                                            <div class="col-12">
                                        <?php endif; ?>
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h3 class="h4">
                                                    <?php if ($book['sort_order_in_series']): ?>
                                                        <small class="text-muted">Книга <?= $book['sort_order_in_series'] ?></small><br>
                                                    <?php endif; ?>
                                                    <?= e($book['title']) ?>
                                                </h3>
                                            </div>
                                            
                                            <?php if ($book['genre']): ?>
                                                <p class="text-muted mb-2"><em><?= e($book['genre']) ?></em></p>
                                            <?php endif; ?>
                                            
                                            <?php if ($book['description']): ?>
                                                <p class="mb-3"><?= nl2br(e($book['description'])) ?></p>
                                            <?php endif; ?>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <a href="<?= SITE_URL ?>/book/<?= e($book['share_token']) ?>" class="btn btn-primary">
                                                        <i class="bi bi-book"></i> Читать
                                                    </a>
                                                </div>
                                                <div class="text-end">
                                                    <?php
                                                    $book_stats = $book_model->getBookStats($book['id'], true);
                                                    ?>
                                                    <small class="text-muted">
                                                        Глав: <?= $book_stats['chapter_count'] ?? 0 ?> | 
                                                        Слов: <?= $book_stats['total_words'] ?? 0 ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <footer class="text-center mt-5 pt-4 border-top">
                        <p class="text-muted">
                            Серия создана в <?= e(APP_NAME) ?> • 
                            Автор: <a href="<?= SITE_URL ?>/author/<?= $author['id'] ?>"><?= e($author['display_name'] ?: $author['username']) ?></a>
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