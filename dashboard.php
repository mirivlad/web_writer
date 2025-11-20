<?php
require_once 'config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$bookModel = new Book($pdo);
$books = $bookModel->findByUser($user_id);

$page_title = "Панель управления";
include 'views/header.php';
?>

<h1>Добро пожаловать, <?= e($_SESSION['display_name']) ?>!</h1>

<div class="grid">
    <article>
        <h2>📚 Мои книги</h2>
        <p>Управляйте вашими книгами и главами</p>
        <a href="books.php" role="button">
            Мои книги (<?= count($books) ?>) 
        </a>
        &nbsp;&nbsp;
        <a href="book_edit.php" role="button">➕ Новая книга</a>
    </article>
    
    <article>
        <h2>📊 Статистика</h2>
        <?php
        $total_chapters = 0;
        $total_words = 0;
        foreach ($books as $book) {
            $total_chapters += $book['chapter_count'];
            $total_words += $book['total_words'];
        }
        ?>
        <p><strong>Книг:</strong> <?= count($books) ?></p>
        <p><strong>Глав:</strong> <?= $total_chapters ?></p>
        <p><strong>Всего слов:</strong> <?= $total_words ?></p>
    </article>
</div>

<?php if (!empty($books)): ?>
<div style="margin-top: 2rem;">
    
    <div class="grid">
        <article>
        <h2>Недавние книги</h2>
            <?php foreach (array_slice($books, 0, 3) as $book): ?>
            <article>
                <h4><?= e($book['title']) ?></h4>
                <p>Глав: <?= $book['chapter_count'] ?> | Слов: <?= $book['total_words'] ?></p>
                <a href="book_edit.php?id=<?= $book['id'] ?>" role="button" class="secondary">
                    Редактировать
                </a>
            </article>
            <?php endforeach; ?>
        </article>
    </div>
</div>
<?php endif; ?>

<?php include 'views/footer.php'; ?>