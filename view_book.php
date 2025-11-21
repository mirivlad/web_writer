<?php
require_once 'config/config.php';
require_once 'includes/parsedown/ParsedownExtra.php';

$Parsedown = new ParsedownExtra();

// Получаем книгу по share_token или id
$share_token = $_GET['share_token'] ?? null;
$book_id = $_GET['id'] ?? null;

$bookModel = new Book($pdo);
$book = null;

if ($share_token) {
    $book = $bookModel->findByShareToken($share_token);
} elseif ($book_id) {
    $book = $bookModel->findById($book_id);
}

if (!$book) {
    http_response_code(404);
    $page_title = "Книга не найдена";
    include 'views/header.php';
    ?>
    <div class="container">
        <article style="text-align: center; padding: 2rem;">
            <h1>Книга не найдена</h1>
            <p>Запрошенная книга не существует или была удалена.</p>
            <a href="index.php" role="button">На главную</a>
        </article>
    </div>
    <?php
    include 'views/footer.php';
    exit;
}

// Получаем опубликованные главы
$chapters = $bookModel->getPublishedChapters($book['id']);
$total_words = array_sum(array_column($chapters, 'word_count'));

// Получаем информацию об авторе
$stmt = $pdo->prepare("SELECT display_name, username FROM users WHERE id = ?");
$stmt->execute([$book['user_id']]);
$author_info = $stmt->fetch(PDO::FETCH_ASSOC);
$author_name = $author_info['display_name'] ?? $author_info['username'] ?? 'Неизвестный автор';

$page_title = $book['title'];
include 'views/header.php';
?>

<div class="container">
    <article style="max-width: 800px; margin: 0 auto;">
        <header style="text-align: center; margin-bottom: 2rem; border-bottom: 2px solid #eee; padding-bottom: 1rem;">
            <?php if ($book['cover_image']): ?>
                <div style="margin-bottom: 1rem;">
                    <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                         alt="<?= e($book['title']) ?>" 
                         style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"
                         onerror="this.style.display='none'">
                </div>
            <?php endif; ?>
            
            <h1 style="margin-bottom: 0.5rem;"><?= e($book['title']) ?></h1>
            <!-- В view_book.php, после информации об авторе -->
            <?php if ($book['series_id']): ?>
                <?php
                $series_stmt = $pdo->prepare("SELECT id, title FROM series WHERE id = ?");
                $series_stmt->execute([$book['series_id']]);
                $series = $series_stmt->fetch();
                ?>
                <?php if ($series): ?>
                    <p style="color: #666; margin-bottom: 0.5rem;">
                        📚 Часть серии: 
                        <a href="view_series.php?id=<?= $series['id'] ?>" style="color: #007bff;">
                            <?= e($series['title']) ?>
                            <?php if ($book['sort_order_in_series']): ?>
                                (Книга <?= $book['sort_order_in_series'] ?>)
                            <?php endif; ?>
                        </a>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
            <p style="color: #666; font-style: italic; margin-bottom: 0.5rem;"><?= e($author_name) ?></p>
            
            <?php if ($book['genre']): ?>
                <p style="color: #666; font-style: italic; margin-bottom: 0.5rem;">
                    Жанр: <?= e($book['genre']) ?>
                </p>
            <?php endif; ?>
            
            <?php if ($book['description']): ?>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                    <p style="margin: 0; font-size: 1.1em;"><?= nl2br(e($book['description'])) ?></p>
                </div>
            <?php endif; ?>
            
            <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; font-size: 0.9em; color: #666;">
                <span>Глав: <?= count($chapters) ?></span>
                <span>Слов: <?= $total_words ?></span>
                <?php if (is_logged_in() && $book['user_id'] == $_SESSION['user_id']): ?>
                    <span>|</span>
                    <a href="books.php" style="color: #007bff;">Вернуться к редактированию</a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Интерактивное оглавление -->
        <?php if (!empty($chapters)): ?>
        <div style="margin: 2rem 0; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007bff;">
            <h3 style="margin-top: 0; color: #007bff;">📖 Оглавление</h3>
			<a name="start"></a>
            <div style="columns: 1;">
                <?php foreach ($chapters as $index => $chapter): ?>
                <div style="break-inside: avoid; margin-bottom: 0.5rem;">
                    <a href="#chapter-<?= $chapter['id'] ?>" 
                       style="text-decoration: none; color: #333; display: block; padding: 0.3rem 0;"
                       onmouseover="this.style.color='#007bff'" 
                       onmouseout="this.style.color='#333'">
                        <span style="color: #666; font-size: 0.9em;"><?= $index + 1 ?>.</span>
                        <?= e($chapter['title']) ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin: 1rem 0; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
            <h3 style="margin: 0 0 0.5rem 0;">Экспорт книги</h3>
            
            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                <a href="export_book.php?share_token=<?= $book['share_token'] ?>&format=pdf" class="adaptive-button secondary" target="_blank">
                    📄 PDF
                </a>
                <a href="export_book.php?share_token=<?= $book['share_token'] ?>&format=docx" class="adaptive-button secondary" target="_blank">
                    📝 DOCX
                </a>
                <a href="export_book.php?share_token=<?= $book['share_token'] ?>&format=html" class="adaptive-button secondary" target="_blank">
                    🌐 HTML
                </a>
                <a href="export_book.php?share_token=<?= $book['share_token'] ?>&format=txt" class="adaptive-button secondary" target="_blank">
                    📄 TXT
                </a>
            </div>
            
            <p style="margin-top: 0.5rem; font-size: 0.9em; color: #666;">
                <strong>Примечание:</strong> Экспортируются только опубликованные главы
            </p>
        </div>

        <?php if (empty($chapters)): ?>
            <div style="text-align: center; padding: 3rem; background: #f9f9f9; border-radius: 5px;">
                <h3>В этой книге пока нет опубликованных глав</h3>
                <p>Автор еще не опубликовал ни одной главы</p>
            </div>
        <?php else: ?>
            <div class="book-content">
                <?php foreach ($chapters as $index => $chapter): ?>
                <section class="chapter" id="chapter-<?= $chapter['id'] ?>" style="margin-bottom: 3rem; scroll-margin-top: 2rem;">
                    <h2 style="border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">
                        <?= e($chapter['title']) ?>
                        <a href="#start" style="text-decoration: none; color: #666; font-size: 0.8em; margin-left: 1rem;">🔗</a>
                    </h2>
                    <div class="chapter-content" style="line-height: 1.6; font-size: 1.1em;">
                        <?= $Parsedown->text($chapter['content']) ?>
                    </div>
                    <div style="margin-top: 1rem; padding-top: 0.5rem; border-top: 1px dashed #eee; color: #666; font-size: 0.9em;">
                        <small>Обновлено: <?= date('d.m.Y', strtotime($chapter['updated_at'])) ?></small>
                        <a href="#top" style="float: right; color: #007bff; text-decoration: none;">↑ Наверх</a>
                    </div>
                </section>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <footer style="margin-top: 3rem; padding-top: 1rem; border-top: 2px solid #eee; text-align: center;">
            <p style="color: #666;">
                Книга создана в <?= e(APP_NAME) ?> • 
                Автор: <?= e($author_name) ?> • 
                <?= date('d.m.Y', strtotime($book['created_at'])) ?>
            </p>
        </footer>
    </article>
</div>

<style>
.book-content {
    line-height: 1.7;
}

.book-content h1, .book-content h2, .book-content h3, .book-content h4, .book-content h5, .book-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.book-content p {
    margin-bottom: 1rem;
    text-align: justify;
}

.book-content blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin-left: 0;
    color: #555;
    font-style: italic;
}

.book-content code {
    background: #f5f5f5;
    padding: 2px 4px;
    border-radius: 3px;
}

.book-content pre {
    background: #f5f5f5;
    padding: 1rem;
    border-radius: 5px;
    overflow-x: auto;
}

.book-content ul, .book-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.book-content table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
}

.book-content th, .book-content td {
    border: 1px solid #ddd;
    padding: 8px 12px;
    text-align: left;
}

.book-content th {
    background: #f5f5f5;
}

/* Адаптивность для оглавления */
@media (max-width: 768px) {
    .book-content {
        font-size: 16px;
        line-height: 1.6;
    }
    
    .book-content h1 {
        font-size: 1.6em;
    }
    
    .book-content h2 {
        font-size: 1.4em;
    }
    
    .book-content h3 {
        font-size: 1.2em;
    }
    
    .book-content pre {
        font-size: 14px;
    }
    
    /* Оглавление в одну колонку на мобильных */
    div[style*="columns: 2"] {
        columns: 1 !important;
    }
}
</style>

<?php include 'views/footer.php'; ?>