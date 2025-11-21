<?php
require_once 'config/config.php';
require_once 'includes/parsedown/ParsedownExtra.php';

$Parsedown = new ParsedownExtra();

$series_id = (int)($_GET['id'] ?? 0);
if (!$series_id) {
    http_response_code(400);
    echo "<h2>Неверный запрос</h2>";
    include 'views/footer.php';
    exit;
}

$seriesModel = new Series($pdo);
$series = $seriesModel->findById($series_id);

if (!$series) {
    http_response_code(404);
    echo "<h2>Серия не найдена</h2>";
    include 'views/footer.php';
    exit;
}

// Получаем только опубликованные книги серии
$books = $seriesModel->getBooksInSeries($series_id, true);

// Получаем информацию об авторе
$stmt = $pdo->prepare("SELECT id, username, display_name FROM users WHERE id = ?");
$stmt->execute([$series['user_id']]);
$author = $stmt->fetch(PDO::FETCH_ASSOC);

// Получаем статистику по опубликованным книгам
$bookModel = new Book($pdo);
$total_words = 0;
$total_chapters = 0;

foreach ($books as $book) {
    $book_stats = $bookModel->getBookStats($book['id'], true); // true - только опубликованные главы
    $total_words += $book_stats['total_words'] ?? 0;
    $total_chapters += $book_stats['chapter_count'] ?? 0;
}

$page_title = $series['title'] . ' — серия книг';
include 'views/header.php';
?>

<div class="container">
    <article style="max-width: 800px; margin: 0 auto;">
        <header style="text-align: center; margin-bottom: 2rem; border-bottom: 2px solid #eee; padding-bottom: 1rem;">
            <h1 style="margin-bottom: 0.5rem;"><?= e($series['title']) ?></h1>
            <p style="color: #666; font-style: italic; margin-bottom: 0.5rem;">
                Серия книг от 
                <a href="author.php?id=<?= $author['id'] ?>"><?= e($author['display_name'] ?: $author['username']) ?></a>
            </p>
            
            <?php if ($series['description']): ?>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin: 1rem 0; text-align: left;">
                    <?= $Parsedown->text($series['description']) ?>
                </div>
            <?php endif; ?>
            
            <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; font-size: 0.9em; color: #666;">
                <span>Книг: <?= count($books) ?></span>
                <span>Глав: <?= $total_chapters ?></span>
                <span>Слов: <?= $total_words ?></span>
            </div>
        </header>

        <?php if (empty($books)): ?>
            <div style="text-align: center; padding: 3rem; background: #f9f9f9; border-radius: 5px;">
                <h3>В этой серии пока нет опубликованных книг</h3>
                <p>Автор еще не опубликовал книги из этой серии</p>
            </div>
        <?php else: ?>
            <div class="series-books">
                <h2 style="text-align: center; margin-bottom: 2rem;">Книги серии</h2>
                
                <?php foreach ($books as $book): ?>
                <article style="display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <?php if ($book['cover_image']): ?>
                        <div style="flex-shrink: 0;">
                            <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                                 alt="<?= e($book['title']) ?>" 
                                 style="max-width: 120px; height: auto; border-radius: 4px; border: 1px solid #ddd;"
                                 onerror="this.style.display='none'">
                        </div>
                    <?php else: ?>
                        <div style="flex-shrink: 0;">
                            <div class="cover-placeholder" style="width: 120px; height: 160px;">📚</div>
                        </div>
                    <?php endif; ?>
                    
                    <div style="flex: 1;">
                        <h3 style="margin-top: 0;">
                            <?php if ($book['sort_order_in_series']): ?>
                                <small style="color: #666;">Книга <?= $book['sort_order_in_series'] ?></small><br>
                            <?php endif; ?>
                            <?= e($book['title']) ?>
                        </h3>
                        
                        <?php if ($book['genre']): ?>
                            <p style="color: #666; margin: 0.5rem 0;"><em><?= e($book['genre']) ?></em></p>
                        <?php endif; ?>
                        
                        <?php if ($book['description']): ?>
                            <p style="margin-bottom: 1rem;"><?= nl2br(e($book['description'])) ?></p>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                            <a href="view_book.php?share_token=<?= e($book['share_token']) ?>" class="adaptive-button">
                                Читать
                            </a>
                            
                            <?php
                            $bookModel = new Book($pdo);
                            $book_stats = $bookModel->getBookStats($book['id'], true); // true - только опубликованные главы
                            ?>
                            
                            <small style="color: #666;">
                                Глав: <?= $book_stats['chapter_count'] ?? 0 ?> | Слов: <?= $book_stats['total_words'] ?? 0 ?>
                            </small>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <footer style="margin-top: 3rem; padding-top: 1rem; border-top: 2px solid #eee; text-align: center;">
            <p style="color: #666;">
                Серия создана в <?= e(APP_NAME) ?> • 
                Автор: <a href="author.php?id=<?= $author['id'] ?>"><?= e($author['display_name'] ?: $author['username']) ?></a>
            </p>
        </footer>
    </article>
</div>

<style>
.series-books article {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.series-books article:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .series-books article {
        flex-direction: column;
        text-align: center;
    }
    
    .series-books .book-cover {
        align-self: center;
    }
}
</style>

<?php include 'views/footer.php'; ?>