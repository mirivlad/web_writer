<?php
// views/books/index.php
include 'views/layouts/header.php';
?>

<h1>Мои книги</h1>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
    <h2 style="margin: 0;">Всего книг: <?= count($books) ?></h2>
    <a href="<?= SITE_URL ?>/books/create" class="action-button primary">➕ Новая книга</a>
</div>

<?php if (empty($books)): ?>
    <article style="text-align: center; padding: 2rem;">
        <h3>У вас пока нет книг</h3>
        <p>Создайте свою первую книгу и начните писать!</p>
        <a href="<?= SITE_URL ?>/books/create" role="button">📖 Создать первую книгу</a>
    </article>
<?php else: ?>
    <div class="grid">
        <?php foreach ($books as $book): ?>
        <article>
            <header>
                <h3 style="margin-bottom: 0.5rem;">
                    <?= e($book['title']) ?>
                    <div style="float: right; display: flex; gap: 3px;">
                        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit" class="compact-button secondary" title="Редактировать книгу">
                            ✏️
                        </a>
                        <a href="<?= SITE_URL ?>/book/<?= $book['share_token'] ?>" class="compact-button secondary" title="Просмотреть книгу" target="_blank">
                            👁️
                        </a>
                    </div>
                </h3>
                <?php if ($book['genre']): ?>
                    <p style="margin: 0; color: var(--muted-color);"><em><?= e($book['genre']) ?></em></p>
                <?php endif; ?>
            </header>
            
            <?php if ($book['description']): ?>
                <p><?= e(mb_strimwidth($book['description'], 0, 200, '...')) ?></p>
            <?php endif; ?>
            
            <footer>
                <div>
                    <small>
                        Глав: <?= $book['chapter_count'] ?> | 
                        Слов: <?= $book['total_words'] ?> | 
                        Статус: <?= $book['published'] ? '✅ Опубликована' : '📝 Черновик' ?>
                    </small>
                </div>
                <div style="margin-top: 0.5rem; display: flex; gap: 5px; flex-wrap: wrap;">
                    <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters" class="adaptive-button secondary">
                        📑 Главы
                    </a>
                    <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>" class="adaptive-button secondary" target="_blank">
                        📄 Экспорт
                    </a>
                </div>
            </footer>
        </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>