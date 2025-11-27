<?php
// views/books/view_public.php
include 'views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-10">
            <article class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <header class="text-center mb-4">
                        <?php if (!empty($book['cover_image'])): ?>
                            <div class="mb-4">
                                <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                                     alt="<?= e($book['title']) ?>" 
                                     class="img-fluid rounded shadow"
                                     style="max-height: 300px;"
                                     onerror="this.style.display='none'">
                            </div>
                        <?php endif; ?>
                        
                        <h1 class="display-5 mb-2"><?= e($book['title']) ?></h1>
                        
                        <p class="lead text-muted mb-2">
                            Автор: <a href="<?= SITE_URL ?>/author/<?= $book['user_id'] ?>" class="text-decoration-none">
                                <?= e($author['display_name']??$author['username']) ?>
                            </a>
                        </p>
                        
                        <?php if (!empty($book['genre'])): ?>
                            <p class="text-muted mb-3">
                                <i class="bi bi-tags"></i> <?= e($book['genre']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($book['description'])): ?>
                            <div class="text-start bg-light p-4 rounded mb-4 ">
                                <?= nl2br(e($book['description'])) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-center gap-4 flex-wrap mb-4">
                            <div class="text-center">
                                <div class="h4 text-primary mb-0"><?= count($chapters) ?></div>
                                <small class="text-muted">Глав</small>
                            </div>
                            <div class="text-center">
                                <div class="h4 text-success mb-0"><?= array_sum(array_column($chapters, 'word_count')) ?></div>
                                <small class="text-muted">Слов</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <?php if (!is_logged_in()): ?>
                                <a href="<?= SITE_URL ?>/export/shared/<?= $book['share_token'] ?>/pdf" class="btn btn-outline-danger" target="_blank">
                                    <i class="bi bi-file-pdf"></i> PDF
                                </a>
                                <a href="<?= SITE_URL ?>/export/shared/<?= $book['share_token'] ?>/docx" class="btn btn-outline-primary" target="_blank">
                                    <i class="bi bi-file-word"></i> DOCX
                                </a>
                                <a href="<?= SITE_URL ?>/export/shared/<?= $book['share_token'] ?>/html" class="btn btn-outline-success" target="_blank">
                                    <i class="bi bi-file-code"></i> HTML
                                </a>
                                <a href="<?= SITE_URL ?>/export/shared/<?= $book['share_token'] ?>/txt" class="btn btn-outline-secondary" target="_blank">
                                    <i class="bi bi-file-text"></i> TXT
                                </a>
                            <?php else: ?>
                                <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/pdf" class="btn btn-outline-danger" target="_blank">
                                    <i class="bi bi-file-pdf"></i> PDF
                                </a>
                                <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/docx" class="btn btn-outline-primary" target="_blank">
                                    <i class="bi bi-file-word"></i> DOCX
                                </a>
                                <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/html" class="btn btn-outline-success" target="_blank">
                                    <i class="bi bi-file-code"></i> HTML
                                </a>
                                <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/txt" class="btn btn-outline-secondary" target="_blank">
                                    <i class="bi bi-file-text"></i> TXT
                                </a>
                            <?php endif; ?>
                        </div>
                    </header>

                    <?php if (empty($chapters)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-file-text fs-1 text-muted"></i>
                            <h3 class="h4 text-muted mt-3">В этой книге пока нет глав</h3>
                            <p class="text-muted">Автор еще не опубликовал содержание книги</p>
                        </div>
                    <?php else: ?>
                        <div class="mb-5">
                            <h3 class="h4 text-center mb-4">
                                <i class="bi bi-list-ul"></i> Оглавление
                            </h3>
                            <div class="list-group">
                                <?php foreach ($chapters as $index => $chapter): ?>
                                    <a href="#chapter-<?= $chapter['id'] ?>" class="list-group-item list-group-item-action border-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>
                                                <strong><?= e($chapter['title']) ?></strong>
                                            </span>
                                            <small class="text-muted"><?= $chapter['word_count'] ?> слов</small>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <hr class="my-5">

                        <?php foreach ($chapters as $index => $chapter): ?>
                            <div class="chapter-content mb-5" id="chapter-<?= $chapter['id'] ?>">
                                <h2 class="border-bottom pb-2 mb-4">
                                    Глава <?= $index + 1 ?>: <?= e($chapter['title']) ?>
                                </h2>
                                
                                <div class="chapter-text" style="line-height: 1.8; font-size: 1.1em;">
                                    <?= $chapter['content'] ?>
                                </div>

                                <div class="text-center mt-4">
                                    <a href="#top" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-arrow-up"></i> Наверх
                                    </a>
                                </div>
                            </div>
                            
                            <?php if ($index < count($chapters) - 1): ?>
                                <hr class="my-5">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <footer class="text-center mt-5 pt-4 border-top">
                        <p class="text-muted">
                            Книга создана в <?= e(APP_NAME) ?> • <?= date('Y') ?>
                        </p>
                    </footer>
                </div>
            </article>
        </div>
    </div>
</div>

<style>
.chapter-text p {
    margin-bottom: 1.5em;
    text-align: justify;
}

.chapter-text .dialogue {
    margin-left: 2rem;
    font-style: italic;
    color: #2c5aa0;
}

.chapter-text blockquote {
    border-left: 4px solid var(--bs-primary);
    padding-left: 1rem;
    margin-left: 0;
    color: #555;
    font-style: italic;
    background: var(--bs-light);
    padding: 1rem;
    border-radius: 0 0.5rem 0.5rem 0;
}

.chapter-text pre {
    background: var(--bs-dark);
    color: var(--bs-light);
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
}

.chapter-text code {
    background: var(--bs-light);
    padding: 0.2em 0.4em;
    border-radius: 0.3rem;
    font-size: 0.9em;
}
</style>

<?php include 'views/layouts/footer.php'; ?>