<?php
include 'views/layouts/header.php';
?>

<h1>Редактирование серии: <?= e($series['title']) ?></h1>

<div class="grid">
    <div>
        <article>
            <h2>Основная информация</h2>
            <form method="post" action="/series/<?= $series['id'] ?>/edit">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <label for="title">
                    Название серии *
                    <input type="text" id="title" name="title" value="<?= e($series['title']) ?>" required>
                </label>
                
                <label for="description">
                    Описание серии
                    <textarea id="description" name="description" rows="4"><?= e($series['description'] ?? '') ?></textarea>
                </label>
                
                <button type="submit" class="primary-btn">Сохранить изменения</button>
            </form>
        </article>

        <article>
            <h2>Добавить книгу в серию</h2>
            <?php 
            $available_books = $bookModel->getBooksNotInSeries($_SESSION['user_id'], $series['id']);
            ?>
            
            <?php if (!empty($available_books)): ?>
                <form method="post" action="/series/<?= $series['id'] ?>/add-book">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <label for="book_id">
                        Выберите книгу
                        <select id="book_id" name="book_id" required>
                            <option value="">-- Выберите книгу --</option>
                            <?php foreach ($available_books as $book): ?>
                                <option value="<?= $book['id'] ?>"><?= e($book['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    
                    <label for="sort_order">
                        Порядковый номер в серии
                        <input type="number" id="sort_order" name="sort_order" value="<?= count($books_in_series) + 1 ?>" min="1">
                    </label>
                    
                    <button type="submit" class="secondary-btn">Добавить в серию</button>
                </form>
            <?php else: ?>
                <p>Все ваши книги уже добавлены в эту серию или у вас нет доступных книг.</p>
                <a href="/books/create" class="primary-btn">Создать новую книгу</a>
            <?php endif; ?>
        </article>
    </div>
    
    <div>
        <article>
            <h2>Книги в серии (<?= count($books_in_series) ?>)</h2>
            
            <?php if (!empty($books_in_series)): ?>
                <div id="series-books-list">
                    <form id="reorder-form" method="post" action="/series/<?= $series['id'] ?>/update-order">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="books-list">
                            <?php foreach ($books_in_series as $index => $book): ?>
                                <div class="book-item" data-book-id="<?= $book['id'] ?>">
                                    <div class="book-drag-handle" style="cursor: move;">☰</div>
                                    <div class="book-info">
                                        <strong><?= e($book['title']) ?></strong>
                                        <small>Порядок: <?= $book['sort_order_in_series'] ?></small>
                                    </div>
                                    <div class="book-actions">
                                        <a href="/books/<?= $book['id'] ?>/edit" class="compact-button">Редактировать</a>
                                        <form method="post" action="/series/<?= $series['id'] ?>/remove-book/<?= $book['id'] ?>" 
                                              style="display: inline;" onsubmit="return confirm('Удалить книгу из серии?')">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <button type="submit" class="compact-button delete-btn">Удалить</button>
                                        </form>
                                    </div>
                                    <input type="hidden" name="order[]" value="<?= $book['id'] ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="submit" class="secondary-btn" id="save-order-btn" style="display: none;">
                            Сохранить порядок
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <p>В этой серии пока нет книг. Добавьте книги с помощью формы слева.</p>
            <?php endif; ?>
        </article>

        <article>
            <h2>Статистика серии</h2>
            <div class="stats-list">
                <p><strong>Количество книг:</strong> <?= count($books_in_series) ?></p>
                <?php
                $total_words = 0;
                $total_chapters = 0;
                foreach ($books_in_series as $book) {
                    $stats = $bookModel->getBookStats($book['id']);
                    $total_words += $stats['total_words'] ?? 0;
                    $total_chapters += $stats['chapter_count'] ?? 0;
                }
                ?>
                <p><strong>Всего глав:</strong> <?= $total_chapters ?></p>
                <p><strong>Всего слов:</strong> <?= $total_words ?></p>
            </div>
        </article>
    </div>
</div>

<style>
.books-list {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
}

.book-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #f0f0f0;
    background: white;
    transition: background-color 0.2s ease;
}

.book-item:last-child {
    border-bottom: none;
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
    padding: 0 10px;
    color: #666;
    font-size: 1.2rem;
}

.book-info {
    flex: 1;
    padding: 0 10px;
}

.book-info strong {
    display: block;
    margin-bottom: 2px;
}

.book-info small {
    color: #666;
    font-size: 0.8rem;
}

.book-actions {
    display: flex;
    gap: 5px;
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
    saveOrderBtn.addEventListener('click', function(e) {
        e.preventDefault();
        clearTimeout(saveTimeout);
        document.getElementById('reorder-form').submit();
    });
    
    // Автоматическое сохранение при изменении порядка
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