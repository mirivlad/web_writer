<?php
// views/dashboard/index.php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Панель управления</h1>
        <div class="btn-group">
            <a href="<?= SITE_URL ?>/books/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Новая книга
            </a>
            <a href="<?= SITE_URL ?>/series/create" class="btn btn-outline-primary">
                <i class="bi bi-collection"></i> Новая серия
            </a>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="card-title text-primary"><?= $total_books ?></h3>
                            <p class="card-text text-muted mb-0">Всего книг</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-journal-bookmark fs-1 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="card-title text-success"><?= $total_chapters ?></h3>
                            <p class="card-text text-muted mb-0">Всего глав</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-file-text fs-1 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="card-title text-warning"><?= number_format($total_words) ?></h3>
                            <p class="card-text text-muted mb-0">Всего слов</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-fonts fs-1 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="card-title text-info"><?= $published_books_count ?></h3>
                            <p class="card-text text-muted mb-0">Опубликовано книг</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-globe fs-1 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Недавние книги -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Недавние книги</h5>
                    <a href="<?= SITE_URL ?>/books" class="btn btn-sm btn-outline-primary">Все книги</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_books)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_books as $book): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-1">
                                            <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit" class="text-decoration-none">
                                                <?= e($book['title']) ?>
                                            </a>
                                        </h6>
                                        <span class="badge <?= $book['published'] ? 'bg-success' : 'bg-warning' ?>">
                                            <?= $book['published'] ? 'Опубликована' : 'Черновик' ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($book['genre']): ?>
                                        <p class="text-muted small mb-1"><em><?= e($book['genre']) ?></em></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($book['description']): ?>
                                        <p class="text-muted small mb-2"><?= e($book['description']) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Глав: <?= $book['chapter_count'] ?? 0 ?> | 
                                            Слов: <?= $book['total_words'] ?? 0 ?>
                                        </small>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-file-text"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($recent_books) < $total_books): ?>
                            <div class="text-center mt-3">
                                <a href="<?= SITE_URL ?>/books" class="btn btn-outline-primary">Все книги</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-journal-bookmark fs-1 text-muted"></i>
                            <p class="text-muted mt-2">У вас пока нет книг</p>
                            <a href="<?= SITE_URL ?>/books/create" class="btn btn-primary">Создать первую книгу</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Мои серии и быстрые действия -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Мои серии</h5>
                    <a href="<?= SITE_URL ?>/series" class="btn btn-sm btn-outline-primary">Все серии</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($series)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($series as $ser): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-1">
                                            <a href="<?= SITE_URL ?>/series/<?= $ser['id'] ?>/edit" class="text-decoration-none">
                                                <?= e($ser['title']) ?>
                                            </a>
                                        </h6>
                                        <span class="badge bg-primary"><?= $ser['book_count'] ?> книг</span>
                                    </div>
                                    
                                    <?php if ($ser['description']): ?>
                                        <p class="text-muted small mb-2"><?= e(mb_strimwidth($ser['description'], 0, 100, '...')) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Книг: <?= $ser['book_count'] ?> | 
                                            Слов: <?= $ser['total_words'] ?>
                                        </small>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= SITE_URL ?>/series/<?= $ser['id'] ?>/edit" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= SITE_URL ?>/series/<?= $ser['id'] ?>/view" class="btn btn-outline-success btn-sm" target="_blank">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-collection fs-1 text-muted"></i>
                            <p class="text-muted mt-2">У вас пока нет серий</p>
                            <a href="<?= SITE_URL ?>/series/create" class="btn btn-primary">Создать первую серию</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="row g-4">            
            <!-- Быстрые действия -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Быстрые действия</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <a href="<?= SITE_URL ?>/books/create" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center py-3">
                                <i class="bi bi-journal-plus fs-4 me-2"></i>
                                <div>
                                    <div>Новая книга</div>
                                    <small class="text-muted">Создать книгу</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= SITE_URL ?>/series/create" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center py-3">
                                <i class="bi bi-collection fs-4 me-2"></i>
                                <div>
                                    <div>Новая серия</div>
                                    <small class="text-muted">Создать серию</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= SITE_URL ?>/books" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center py-3">
                                <i class="bi bi-journal-bookmark fs-4 me-2"></i>
                                <div>
                                    <div>Все книги</div>
                                    <small class="text-muted">Управление</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= SITE_URL ?>/profile" class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center py-3">
                                <i class="bi bi-person fs-4 me-2"></i>
                                <div>
                                    <div>Профиль</div>
                                    <small class="text-muted">Настройки</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Дополнительная статистика -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Прогресс писателя</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2 col-6 mb-3">
                            <div class="h4 text-primary mb-1"><?= $total_books ?></div>
                            <small class="text-muted">Книг</small>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="h4 text-success mb-1"><?= $total_chapters ?></div>
                            <small class="text-muted">Глав</small>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="h4 text-warning mb-1"><?= number_format($total_words) ?></div>
                            <small class="text-muted">Слов</small>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="h4 text-info mb-1"><?= $published_books_count ?></div>
                            <small class="text-muted">Опубликовано</small>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="h4 text-secondary mb-1"><?= $total_books > 0 ? round($total_words / $total_books) : 0 ?></div>
                            <small class="text-muted">Слов/книга</small>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="h4 text-dark mb-1"><?= $total_chapters > 0 ? round($total_words / $total_chapters) : 0 ?></div>
                            <small class="text-muted">Слов/глава</small>
                        </div>
                    </div>
                    
                    <?php if ($total_words > 0): ?>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Прогресс этого месяца</small>
                                <small><?= number_format($total_words) ?> слов</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?= min(100, ($total_words / 50000) * 100) ?>%"></div>
                            </div>
                            <small class="text-muted">Цель: 50,000 слов в месяц</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>