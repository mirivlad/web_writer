<?php
require_once 'config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$bookModel = new Book($pdo);
$books = $bookModel->findByUser($user_id);

$page_title = "Мои книги";
include 'views/header.php';
?>

<h1>Мои книги</h1>

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

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <h2 style="margin: 0;">Всего книг: <?= count($books) ?></h2>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="book_edit.php" class="action-button primary">➕ Новая книга</a>
        <?php if (!empty($books)): ?>
            <button type="button" onclick="showDeleteConfirmation()" class="action-button delete">
                🗑️ Удалить все книги
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($books)): ?>
    <article style="text-align: center; padding: 2rem;">
        <h3>У вас пока нет книг</h3>
        <p>Создайте свою первую книгу и начните писать!</p>
        <a href="book_edit.php" role="button">📖 Создать первую книгу</a>
    </article>
<?php else: ?>
    <div class="grid">
        <?php foreach ($books as $book): ?>
        <article>
            <header>
                <h3><?= e($book['title']) ?></h3>
                <?php if ($book['genre']): ?>
                    <small style="color: #666;"><?= e($book['genre']) ?></small>
                <?php endif; ?>
            </header>
            
            <?php if ($book['description']): ?>
                <p><?= e(mb_strimwidth($book['description'], 0, 150, '...')) ?></p>
            <?php endif; ?>
            
                <footer style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <small>
                            Глав: <?= $book['chapter_count'] ?> | 
                            Слов: <?= $book['total_words'] ?>
                        </small>
                    </div>
                    <div style="display: flex; gap: 3px;">
                        <a href="export_book.php?book_id=<?= $book['id'] ?>&format=pdf" class="compact-button secondary" title="Экспорт в PDF" target="_blank">
                            📄
                        </a>
                        <a href="view_book.php?share_token=<?= $book['share_token'] ?>" class="compact-button secondary" title="Просмотреть книгу" target="_blank">
                            👁️
                        </a>
                        <a href="book_edit.php?id=<?= $book['id'] ?>" class="compact-button secondary" title="Редактировать книгу">
                            ✏️
                        </a>
                        <a href="chapters.php?book_id=<?= $book['id'] ?>" class="compact-button secondary" title="Просмотр глав">
                            📑
                        </a>
                        <form method="post" action="book_delete.php" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить книгу «<?= e($book['title']) ?>»? Все главы также будут удалены.');">
                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <button type="submit" class="compact-button secondary" style="background: #ff4444; border-color: #ff4444; color: white;" title="Удалить книгу">
                                🗑️
                            </button>
                        </form>
                    </div>
                </footer>
        </article>
        <?php endforeach; ?>
    </div>
    <!-- Модальное окно для удаления всех книг -->
    <dialog id="deleteAllDialog" style="border-radius: 8px; padding: 20px; max-width: 500px; background-color: #fff;">
        <h3 style="margin-top: 0;">Удалить все книги?</h3>
        <p>Это действие удалит все ваши книги и все связанные с ними главы. Это действие нельзя отменить.</p>
        
        <form method="post" action="book_delete_all.php" style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <button type="button" onclick="closeDeleteDialog()" class="secondary" style="flex: 1;">
                ❌ Отмена
            </button>
            <button type="submit" class="contrast" style="flex: 1; background: #ff4444; border-color: #ff4444; color: white;">
                🗑️ Удалить все
            </button>
        </form>
    </dialog>

    <script>
    function showDeleteConfirmation() {
        const dialog = document.getElementById('deleteAllDialog');
        dialog.showModal();
    }

    function closeDeleteDialog() {
        const dialog = document.getElementById('deleteAllDialog');
        dialog.close();
    }

    // Закрытие диалога по клику вне его области
    document.getElementById('deleteAllDialog').addEventListener('click', function(event) {
        if (event.target === this) {
            closeDeleteDialog();
        }
    });
    </script>
<?php endif; ?>

<?php include 'views/footer.php'; ?>