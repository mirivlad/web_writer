<?php
require_once 'config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$bookModel = new Book($pdo);
$seriesModel = new Series($pdo);

$books = $bookModel->findByUser($user_id);
$series = $seriesModel->findByUser($user_id);

// Статистика по книгам
$total_chapters = 0;
$total_words = 0;
foreach ($books as $book) {
    $total_chapters += $book['chapter_count'];
    $total_words += $book['total_words'];
}

// Статистика по сериям
$series_stats = [
    'total_series' => count($series),
    'series_with_books' => 0,
    'total_books_in_series' => 0
];

foreach ($series as $ser) {
    $series_books = $seriesModel->getBooksInSeries($ser['id']);
    $series_stats['total_books_in_series'] += count($series_books);
    if (count($series_books) > 0) {
        $series_stats['series_with_books']++;
    }
}

$page_title = "Панель управления";
include 'views/header.php';
?>

<h1>Добро пожаловать, <?= e($_SESSION['display_name']) ?>!</h1>

<div style="margin-bottom: 1rem;">
    <a href="profile.php" class="adaptive-button secondary">✏️ Редактировать профиль</a>
</div>

<div class="grid">
    <article>
        <h2>📚 Мои книги</h2>
        <p>Управляйте вашими книгами и главами</p>
        <div class="dashboard-buttons">
            <a href="books.php" role="button" class="dashboard-button">
                Мои книги (<?= count($books) ?>) 
            </a>
            <a href="book_edit.php" role="button" class="dashboard-button new">
                ➕ Новая книга
            </a>
        </div>
    </article>
    
    <article>
        <h2>📊 Статистика</h2>
        <div class="stats-list">
            <p><strong>Книг:</strong> <?= count($books) ?></p>
            <p><strong>Глав:</strong> <?= $total_chapters ?></p>
            <p><strong>Всего слов:</strong> <?= $total_words ?></p>
            <?php if ($total_words > 0): ?>
                <p><strong>Средняя глава:</strong> <?= round($total_words / max(1, $total_chapters)) ?> слов</p>
            <?php endif; ?>
        </div>
    </article>
    
    <article>
        <h2>📖 Мои серии</h2>
        <p>Управляйте сериями книг</p>
        <div class="dashboard-buttons">
            <a href="series.php" role="button" class="dashboard-button">
                Мои серии (<?= $series_stats['total_series'] ?>)
            </a>
            <a href="series_edit.php" role="button" class="dashboard-button new">
                ➕ Новая серия
            </a>
        </div>
        
        <?php if ($series_stats['total_series'] > 0): ?>
            <div class="series-stats">
                <p><strong>Книг в сериях:</strong> <?= $series_stats['total_books_in_series'] ?></p>
                <p><strong>Заполненных серий:</strong> <?= $series_stats['series_with_books'] ?></p>
            </div>
        <?php endif; ?>
    </article>
</div>

<?php if (!empty($books)): ?>
<div class="dashboard-section">
    <h2>Недавние книги</h2>
    <div class="grid">
        <?php foreach (array_slice($books, 0, 3) as $book): ?>
        <article class="dashboard-item">
            <h4>
                <?= e($book['title']) ?>
                <?php if ($book['series_id']): ?>
                    <?php
                    $series_stmt = $pdo->prepare("SELECT title FROM series WHERE id = ?");
                    $series_stmt->execute([$book['series_id']]);
                    $series_title = $series_stmt->fetch()['title'] ?? '';
                    ?>
                    <?php if ($series_title): ?>
                        <br><small style="color: #007bff;">📚 <?= e($series_title) ?></small>
                    <?php endif; ?>
                <?php endif; ?>
            </h4>
            <p>Глав: <?= $book['chapter_count'] ?> | Слов: <?= $book['total_words'] ?></p>
            <div class="action-buttons">
                <a href="book_edit.php?id=<?= $book['id'] ?>" role="button" class="compact-button secondary">
                    Редактировать
                </a>
                <a href="chapters.php?book_id=<?= $book['id'] ?>" role="button" class="compact-button secondary">
                    Главы
                </a>
                <a href="view_book.php?share_token=<?= $book['share_token'] ?>" role="button" class="compact-button secondary" target="_blank">
                    Просмотр
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    
    <?php if (count($books) > 3): ?>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="books.php" role="button" class="secondary">📚 Показать все книги (<?= count($books) ?>)</a>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (!empty($series)): ?>
<div class="dashboard-section">
    <h2>Недавние серии</h2>
    <div class="grid">
        <?php foreach (array_slice($series, 0, 3) as $ser): ?>
        <article class="dashboard-item">
            <h4><?= e($ser['title']) ?></h4>
            
            <?php
            $books_in_series = $seriesModel->getBooksInSeries($ser['id']);
            $series_words = 0;
            $series_chapters = 0;
            
            foreach ($books_in_series as $book) {
                $book_stats = $bookModel->getBookStats($book['id']);
                $series_words += $book_stats['total_words'] ?? 0;
                $series_chapters += $book_stats['chapter_count'] ?? 0;
            }
            ?>
            
            <p>Книг: <?= count($books_in_series) ?> | Глав: <?= $series_chapters ?> | Слов: <?= $series_words ?></p>
            
            <div class="action-buttons">
                <a href="series_edit.php?id=<?= $ser['id'] ?>" role="button" class="compact-button secondary">
                    Редактировать
                </a>
                <a href="view_series.php?id=<?= $ser['id'] ?>" role="button" class="compact-button secondary" target="_blank">
                    Просмотр
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    
    <?php if (count($series) > 3): ?>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="series.php" role="button" class="secondary">📖 Показать все серии (<?= count($series) ?>)</a>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (empty($books) && empty($series)): ?>
<div class="welcome-message">
    <h3>Добро пожаловать в <?= e(APP_NAME) ?>!</h3>
    <p>Начните создавать свои литературные произведения</p>
    <div class="welcome-buttons">
        <a href="book_edit.php" role="button" class="contrast">📖 Создать первую книгу</a>
        <a href="series_edit.php" role="button" class="secondary">📚 Создать первую серию</a>
    </div>
    <div style="margin-top: 1.5rem;">
        <a href="profile.php" role="button" class="secondary">✏️ Настроить профиль</a>
    </div>
</div>
<?php endif; ?>

<?php include 'views/footer.php'; ?>