<?php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Редактирование серии: <?= e($series['title']) ?></h1>
        <a href="<?= SITE_URL ?>/series" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к сериям
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Основная информация</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="/series/<?= $series['id'] ?>/edit">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Название серии *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= e($series['title']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание серии</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4"><?= e($series['description'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Сохранить изменения
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Добавить книгу в серию</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($available_books)): ?>
                        <form method="post" action="/series/<?= $series['id'] ?>/add-book">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            
                            <div class="mb-3">
                                <label for="book_id" class="form-label">Выберите книгу</label>
                                <select class="form-select" id="book_id" name="book_id" required>
                                    <option value="">-- Выберите книгу --</option>
                                    <?php foreach ($available_books as $book): ?>
                                        <option value="<?= $book['id'] ?>"><?= e($book['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Порядковый номер в серии</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?= count($books_in_series) + 1 ?>" min="1">
                            </div>
                            
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-plus-circle"></i> Добавить в серию
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="bi bi-journal-bookmark fs-1 text-muted"></i>
                            <p class="text-muted mt-2">Все ваши книги уже добавлены в эту серию или у вас нет доступных книг.</p>
                            <a href="/books/create" class="btn btn-primary">Создать новую книгу</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Книги в серии (<?= count($books_in_series) ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($books_in_series)): ?>
                        <div id="series-books-list">
                            <form id="reorder-form" method="post" action="/series/<?= $series['id'] ?>/update-order">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                
                                <div class="books-list">
                                    <?php foreach ($books_in_series as $index => $book): ?>
                                        <div class="book-item border rounded p-3 mb-2 d-flex align-items-center" 
                                             data-book-id="<?= $book['id'] ?>">
                                            <div class="book-drag-handle text-muted me-3" style="cursor: move;">
                                                <i class="bi bi-grip-vertical"></i>
                                            </div>
                                            <div class="book-info flex-grow-1">
                                                <strong><?= e($book['title']) ?></strong>
                                                <br>
                                                <small class="text-muted">Порядок: <?= $book['sort_order_in_series'] ?></small>
                                            </div>
                                            <div class="book-actions ms-3">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/books/<?= $book['id'] ?>/edit" class="btn btn-outline-primary" title="Редактировать">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="post" action="/series/<?= $series['id'] ?>/remove-book/<?= $book['id'] ?>" 
                                                          onsubmit="return confirm('Удалить книгу из серии?')" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                        <button type="submit" class="btn btn-outline-danger" title="Удалить из серии">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <input type="hidden" name="order[]" value="<?= $book['id'] ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <button type="submit" class="btn btn-outline-success w-100 mt-3" id="save-order-btn" style="display: none;">
                                    <i class="bi bi-check-circle"></i> Сохранить порядок
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-journal-bookmark fs-1 text-muted"></i>
                            <p class="text-muted mt-2">В этой серии пока нет книг.</p>
                            <p class="text-muted small">Добавьте книги с помощью формы слева.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.book-item {
    transition: background-color 0.2s ease;
    background: white;
}

.book-item:hover {
    background: #f8f9fa;
}

.book-item.sortable-ghost {
    opacity: 0.4;
}

.book-item.sortable-chosen {
    background: #e3f2fd;
}

.book-drag-handle {
    font-size: 1.2rem;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const booksList = document.querySelector('.books-list');
    const saveOrderBtn = document.getElementById('save-order-btn');
    
    if (booksList) {
        const sortable = new Sortable(booksList, {
            handle: '.book-drag-handle',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            animation: 150,
            onUpdate: function() {
                saveOrderBtn.style.display = 'block';
            }
        });
    }
    
    // Автосохранение порядка через 2 секунды после изменения
    let saveTimeout;
    booksList.addEventListener('sortupdate', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            document.getElementById('reorder-form').submit();
        }, 2000);
    });
});
</script>

<?php
include 'views/layouts/footer.php';
?>