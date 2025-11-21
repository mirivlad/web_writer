<?php
require_once 'config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$book_id = $_GET['book_id'] ?? null;

if (!$book_id) {
    $_SESSION['error'] = "Не указана книга";
    redirect('books.php');
}

$bookModel = new Book($pdo);
$chapterModel = new Chapter($pdo);


if (!$bookModel->userOwnsBook($book_id, $user_id)) {
    $_SESSION['error'] = "У вас нет доступа к этой книге";
    redirect('books.php');
}

// Получаем информацию о книге и главах
$book = $bookModel->findById($book_id);
$chapters = $chapterModel->findByBook($book_id);

$page_title = "Главы книги: " . e($book['title']);
include 'views/header.php';
?>

<div style="margin-bottom: 1rem;">
    <h1 style="margin: 0 0 0.5rem 0; font-size: 1.5rem;">Главы книги: <?= e($book['title']) ?></h1>
    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
        <a href="chapter_edit.php?book_id=<?= $book_id ?>" class="adaptive-button">➕ Новая глава</a>
        <a href="book_edit.php?id=<?= $book_id ?>" class="adaptive-button secondary">✏️ Редактировать книгу</a>
        <a href="view_book.php?share_token=<?= $book['share_token'] ?>" class="adaptive-button secondary" target="_blank">👁️ Просмотреть книгу</a>
        <a href="books.php" class="adaptive-button secondary">📚 Все книги</a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= e($_SESSION['success']) ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?= e($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<?php if (empty($chapters)): ?>
    <div style="text-align: center; padding: 2rem; background: #f9f9f9; border-radius: 5px; margin-top: 1rem;">
        <h3>В этой книге пока нет глав</h3>
        <p>Создайте первую главу для вашей книги</p>
        <a href="chapter_edit.php?book_id=<?= $book_id ?>" class="adaptive-button">📝 Создать первую главу</a>
    </div>
<?php else: ?>
    <div style="overflow-x: auto; margin-top: 1rem;">
        <table class="compact-table">
            <thead>
                <tr>
                    <th style="width: 5%;">№</th>
                    <th style="width: 40%;">Название главы</th>
                    <th style="width: 15%;">Статус</th>
                    <th style="width: 10%;">Слов</th>
                    <th style="width: 20%;">Обновлено</th>
                    <th style="width: 10%;">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chapters as $index => $chapter): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <strong><?= e($chapter['title']) ?></strong>
                        <?php if ($chapter['description']): ?>
                            <br><small style="color: #666;"><?= e(mb_strimwidth($chapter['description'], 0, 100, '...')) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="color: <?= $chapter['status'] == 'published' ? 'green' : 'orange' ?>">
                            <?= $chapter['status'] == 'published' ? '✅ Опубликована' : '📝 Черновик' ?>
                        </span>
                    </td>
                    <td><?= $chapter['word_count'] ?></td>
                    <td>
                        <small><?= date('d.m.Y H:i', strtotime($chapter['updated_at'])) ?></small>
                    </td>
                    <td>
                        <div style="display: flex; gap: 3px; flex-wrap: wrap;">
                            <a href="chapter_edit.php?id=<?= $chapter['id'] ?>" class="compact-button secondary" title="Редактировать">
                                ✏️
                            </a>
                            <form method="post" action="chapter_delete.php" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить эту главу? Это действие нельзя отменить.');">
                                <input type="hidden" name="chapter_id" value="<?= $chapter['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <button type="submit" class="compact-button secondary" style="background: #ff4444; border-color: #ff4444; color: white;" title="Удалить">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1rem; padding: 0.5rem; background: #f5f5f5; border-radius: 3px;">
        <strong>Статистика:</strong> 
        Всего глав: <?= count($chapters) ?> | 
        Всего слов: <?= array_sum(array_column($chapters, 'word_count')) ?> |
        Опубликовано: <?= count(array_filter($chapters, function($ch) { return $ch['status'] == 'published'; })) ?>
    </div>
<?php endif; ?>

<?php include 'views/footer.php'; ?>