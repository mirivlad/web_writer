<?php
// views/books/index.php
include 'views/layouts/header.php';
?>

<h1>Мои книги <small style="color: #ccc; font-size:1rem;">(Всего книг: <?= count($books) ?>)</small></h1>


<div style="display: flex; justify-content: right; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
    <a href="<?= SITE_URL ?>/books/create" class="action-button primary">➕ Новая книга</a>
    <?php if (!empty($books)): ?>
        <a href="#" onclick="showDeleteAllConfirmation()" class="action-button delete">🗑️ Удалить все книги</a>
    <?php endif; ?>
</div>

<?php if (empty($books)): ?>
    <article style="text-align: center; padding: 2rem;">
        <h3>У вас пока нет книг</h3>
        <p>Создайте свою первую книгу и начните писать!</p>
        <a href="<?= SITE_URL ?>/books/create" role="button">📖 Создать первую книгу</a>
    </article>
<?php else: ?>
    <div class="books-grid">
        <?php foreach ($books as $book): ?>
            <article class="book-card">
                <!-- Обложка книги -->
                <div class="book-cover-container">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                             alt="<?= e($book['title']) ?>" 
                             class="book-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="cover-placeholder" style="display: none;">
                            📚
                        </div>
                    <?php else: ?>
                        <div class="cover-placeholder">
                            📚
                        </div>
                    <?php endif; ?>
                    
                    <!-- Статус книги -->
                    <div class="book-status <?= $book['published'] ? 'published' : 'draft' ?>">
                        <?= $book['published'] ? '✅' : '📝' ?>
                    </div>
                </div>

                <!-- Информация о книге -->
                <div class="book-info">
                    <h3 class="book-title">
                        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit">
                            <?= e($book['title']) ?>
                        </a>
                    </h3>
                    
                    <?php if (!empty($book['genre'])): ?>
                        <p class="book-genre"><?= e($book['genre']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($book['description'])): ?>
                        <p class="book-description">
                            <?= e(mb_strimwidth($book['description'], 0, 120, '...')) ?>
                        </p>
                    <?php endif; ?>
                    
                    <!-- Статистика -->
                    <div class="book-stats">
                        <span class="stat-item">
                            <strong><?= $book['chapter_count'] ?? 0 ?></strong> глав
                        </span>
                        <span class="stat-item">
                            <strong><?= number_format($book['total_words'] ?? 0) ?></strong> слов
                        </span>
                    </div>
                    
                    <!-- Действия -->
                    <div class="book-actions">
                        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit" class="compact-button primary-btn">
                            ✏️ Редактировать
                        </a>
                        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters" class="compact-button secondary-btn">
                            📑 Главы
                        </a>
                        <a href="<?= SITE_URL ?>/book/<?= $book['share_token'] ?>" class="compact-button secondary-btn" target="_blank">
                            👁️ Просмотр
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- Статистика внизу -->
    <div class="books-stats-footer">
        <strong>Общая статистика:</strong> 
        Книг: <?= count($books) ?> | 
        Глав: <?= array_sum(array_column($books, 'chapter_count')) ?> | 
        Слов: <?= number_format(array_sum(array_column($books, 'total_words'))) ?> |
        Опубликовано: <?= count(array_filter($books, function($book) { return $book['published']; })) ?>
    </div>
<?php endif; ?>
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