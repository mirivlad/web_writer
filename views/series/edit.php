<?php
// views/series/edit.php
include 'views/layouts/header.php';
?>

<h1>Редактирование серии: <?= e($series['title']) ?></h1>

<?php if (isset($error) && $error): ?>
    <div class="alert alert-error">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div style="max-width: 100%; margin-bottom: 1rem;">
        <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Название серии *
        </label>
        <input type="text" id="title" name="title" 
               value="<?= e($series['title']) ?>" 
               placeholder="Введите название серии" 
               style="width: 100%; margin-bottom: 1.5rem;" 
               required>
        
        <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Описание серии
        </label>
        <textarea id="description" name="description" 
                  placeholder="Описание сюжета серии, общая концепция..." 
                  rows="6"
                  style="width: 100%;"><?= e($series['description']) ?></textarea>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="submit" class="contrast">
            💾 Сохранить изменения
        </button>
        
        <a href="<?= SITE_URL ?>/series" role="button" class="secondary">
            ❌ Отмена
        </a>
    </div>
</form>

<?php if ($series): ?>
<div style="margin-top: 3rem;">
    <h3>Книги в этой серии</h3>
    
    <?php if (empty($books_in_series)): ?>
        <div style="text-align: center; padding: 2rem; background: var(--card-background-color); border-radius: 5px;">
            <p>В этой серии пока нет книг.</p>
            <a href="<?= SITE_URL ?>/books" class="adaptive-button">📚 Добавить книги</a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="compact-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">Порядок</th>
                        <th style="width: 40%;">Название книги</th>
                        <th style="width: 20%;">Жанр</th>
                        <th style="width: 15%;">Статус</th>
                        <th style="width: 15%;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books_in_series as $book): ?>
                    <tr>
                        <td><?= $book['sort_order_in_series'] ?></td>
                        <td>
                            <strong><?= e($book['title']) ?></strong>
                            <?php if ($book['description']): ?>
                                <br><small style="color: var(--muted-color);"><?= e(mb_strimwidth($book['description'], 0, 100, '...')) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($book['genre']) ?></td>
                        <td>
                            <span style="color: <?= $book['published'] ? 'green' : 'orange' ?>">
                                <?= $book['published'] ? '✅ Опубликована' : '📝 Черновик' ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit" class="compact-button secondary">
                                Редактировать
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        // Вычисляем общую статистику
        $total_chapters = 0;
        $total_words = 0;
        foreach ($books_in_series as $book) {
            $bookModel = new Book($pdo);
            $stats = $bookModel->getBookStats($book['id']);
            $total_chapters += $stats['chapter_count'] ?? 0;
            $total_words += $stats['total_words'] ?? 0;
        }
        ?>
        
        <div style="margin-top: 1rem; padding: 0.5rem; background: var(--card-background-color); border-radius: 3px;">
            <strong>Статистика серии:</strong> 
            Книг: <?= count($books_in_series) ?> | 
            Глав: <?= $total_chapters ?> |
            Слов: <?= $total_words ?>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top: 2rem; text-align: center;">
    <form method="post" action="<?= SITE_URL ?>/series/<?= $series['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить серию «<?= e($series['title']) ?>»? Книги останутся, но будут убраны из серии.');">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <button type="submit" class="button" style="background: #ff4444; border-color: #ff4444; color: white;">
            🗑️ Удалить серию
        </button>
    </form>
</div>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>