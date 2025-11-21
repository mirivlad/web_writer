<?php
require_once 'config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$seriesModel = new Series($pdo);
$series = $seriesModel->findByUser($user_id);

// Получаем статистику для каждой серии отдельно
foreach ($series as &$ser) {
    $stats = $seriesModel->getSeriesStats($ser['id'], $user_id);
    $ser['book_count'] = $stats['book_count'] ?? 0;
    $ser['total_words'] = $stats['total_words'] ?? 0;
}
unset($ser); 

$page_title = "Мои серии книг";
include 'views/header.php';
?>

<h1>Мои серии книг</h1>

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
    <h2 style="margin: 0;">Всего серий: <?= count($series) ?></h2>
    <a href="series_edit.php" class="action-button primary">➕ Новая серия</a>
</div>

<?php if (empty($series)): ?>
    <article style="text-align: center; padding: 2rem;">
        <h3>У вас пока нет серий книг</h3>
        <p>Создайте свою первую серию для организации книг!</p>
        <a href="series_edit.php" role="button">📚 Создать первую серию</a>
    </article>
<?php else: ?>
    <div class="grid">
        <?php foreach ($series as $ser): ?>
        <article>
            <header>
                <h3>
                    <?= e($ser['title']) ?>
                    <div style="display: flex; gap: 3px; float:right;">
                        <a href="series_edit.php?id=<?= $ser['id'] ?>" class="compact-button secondary" title="Редактировать серию">
                            ✏️
                        </a>
                        <a href="view_series.php?id=<?= $ser['id'] ?>" class="compact-button secondary" title="Просмотреть серию">
                            👁️
                        </a>
                        <form method="post" action="series_delete.php" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить серию «<?= e($ser['title']) ?>»? Книги останутся, но будут убраны из серии.');">
                            <input type="hidden" name="series_id" value="<?= $ser['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <button type="submit" class="compact-button secondary" style="background: #ff4444; border-color: #ff4444; color: white;" title="Удалить серию">
                                🗑️
                            </button>
                        </form>
                    </div>
                </h3>
            </header>
            
            <?php if ($ser['description']): ?>
                <p><?= e(mb_strimwidth($ser['description'], 0, 200, '...')) ?></p>
            <?php endif; ?>
            
            <footer>
                <div>
                    <small>
                        Книг: <?= $ser['book_count'] ?> | 
                        Слов: <?= $ser['total_words'] ?>
                    </small>
                </div>
                <div style="margin-top: 0.5rem;">
                    <a href="view_series.php?id=<?= $ser['id'] ?>" class="adaptive-button secondary">
                        📖 Смотреть книги
                    </a>
                </div>
            </footer>
        </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'views/footer.php'; ?>