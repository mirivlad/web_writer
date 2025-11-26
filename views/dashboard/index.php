<?php
// views/dashboard/index.php
include 'views/layouts/header.php';
?>

<h1>Панель управления</h1>

<div class="grid" style="margin-bottom: 2rem;">
    <article style="text-align: center;">
        <h2>📚 Книги</h2>
        <div style="font-size: 2rem; font-weight: bold; color: var(--primary);">
            <?= $total_books ?>
        </div>
        <small>Всего книг</small>
    </article>
    
    <article style="text-align: center;">
        <h2>📑 Главы</h2>
        <div style="font-size: 2rem; font-weight: bold; color: var(--success);">
            <?= $total_chapters ?>
        </div>
        <small>Всего глав</small>
    </article>
    
    <article style="text-align: center;">
        <h2>📝 Слова</h2>
        <div style="font-size: 2rem; font-weight: bold; color: var(--warning);">
            <?= number_format($total_words) ?>
        </div>
        <small>Всего слов</small>
    </article>
    
    <article style="text-align: center;">
        <h2>🌐 Публикации</h2>
        <div style="font-size: 2rem; font-weight: bold; color: var(--info);">
            <?= $published_books_count ?>
        </div>
        <small>Опубликовано книг</small>
    </article>
</div>

<div class="grid">
    <div>
        <h2>Недавние книги</h2>
        <?php if (!empty($recent_books)): ?>
            <?php foreach ($recent_books as $book): ?>
                <article style="margin-bottom: 1em; padding-top: 0.5em;">
                    <h3 style="margin-bottom: 0.5rem; margin-top: 0.5em;">
                        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit">
                            <?= e($book['title']) ?>
                        </a>
                    </h3>
                    <?php if ($book['genre']): ?>
                        <p style="margin: 0; color: var(--muted-color); font-size:small;"><em><?= e($book['genre']) ?></em></p>
                    <?php endif; ?>
                    <?php if ($book['description']): ?>
                        <p style="margin: 0; color: var(--muted-color);"><?= e($book['description']) ?></p>
                    <?php endif; ?>
                    <footer>
                        <small>
                            Глав: <?= $book['chapter_count'] ?? 0 ?> | 
                            Слов: <?= $book['total_words'] ?? 0 ?> |
                            Статус: <?= $book['published'] ? '✅ Опубликована' : '📝 Черновик' ?>
                        </small>
                    </footer>
                </article>
            <?php endforeach; ?>
            
            <?php if (count($recent_books) < count($books)): ?>
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="<?= SITE_URL ?>/books" class="button">Все книги</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <article>
                <p>У вас пока нет книг.</p>
                <a href="<?= SITE_URL ?>/books/create" class="button">Создать первую книгу</a>
            </article>
        <?php endif; ?>
    </div>
    
    <div>
        <h2>Мои серии</h2>
        <?php if (!empty($series)): ?>
            <?php foreach ($series as $ser): ?>
                <article>
                    <h3 style="margin-bottom: 0.5rem;">
                        <a href="<?= SITE_URL ?>/series/<?= $ser['id'] ?>/edit">
                            <?= e($ser['title']) ?>
                        </a>
                    </h3>
                    <?php if ($ser['description']): ?>
                        <p><?= e(mb_strimwidth($ser['description'], 0, 100, '...')) ?></p>
                    <?php endif; ?>
                    <footer>
                        <small>
                            Книг: <?= $ser['book_count'] ?> | 
                            Слов: <?= $ser['total_words'] ?>
                        </small>
                    </footer>
                </article>
            <?php endforeach; ?>
            
            <div style="text-align: center; margin-top: 1rem;">
                <a href="<?= SITE_URL ?>/series" class="button">Все серии</a>
            </div>
        <?php else: ?>
            <article>
                <p>У вас пока нет серий.</p>
                <a href="<?= SITE_URL ?>/series/create" class="button">Создать первую серию</a>
            </article>
        <?php endif; ?>
        
        <h2 style="margin-top: 2rem;">Быстрые действия</h2>
        <div class="button-group">
            <a href="<?= SITE_URL ?>/books/create" class="button">📖 Новая книга</a>
            <a href="<?= SITE_URL ?>/series/create" class="button secondary">📚 Новая серия</a>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>