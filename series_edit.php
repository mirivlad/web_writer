<?php
require_once 'config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$seriesModel = new Series($pdo);

$series_id = $_GET['id'] ?? null;
$series = null;
$is_edit = false;

if ($series_id) {
    $series = $seriesModel->findById($series_id);
    if (!$series || !$seriesModel->userOwnsSeries($series_id, $user_id)) {
        $_SESSION['error'] = "Серия не найдена или у вас нет доступа";
        redirect('series.php');
    }
    $is_edit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Ошибка безопасности";
        redirect($is_edit ? "series_edit.php?id=$series_id" : 'series_edit.php');
    }
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title)) {
        $_SESSION['error'] = "Название серии обязательно";
    } else {
        $data = [
            'title' => $title,
            'description' => $description,
            'user_id' => $user_id
        ];
        
        if ($is_edit) {
            $success = $seriesModel->update($series_id, $data);
            $message = $success ? "Серия успешно обновлена" : "Ошибка при обновлении серии";
        } else {
            $success = $seriesModel->create($data);
            $message = $success ? "Серия успешно создана" : "Ошибка при создании серии";
            
            if ($success) {
                $new_series_id = $pdo->lastInsertId();
                redirect("series_edit.php?id=$new_series_id");
            }
        }
        
        if ($success) {
            $_SESSION['success'] = $message;
            redirect('series.php');
        } else {
            $_SESSION['error'] = $message;
        }
    }
}

$page_title = $is_edit ? "Редактирование серии" : "Создание новой серии";
include 'views/header.php';
?>

<h1><?= $is_edit ? "Редактирование серии" : "Создание новой серии" ?></h1>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?= e($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div style="max-width: 100%; margin-bottom: 1rem;">
        <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Название серии *
        </label>
        <input type="text" id="title" name="title" 
               value="<?= e($series['title'] ?? $_POST['title'] ?? '') ?>" 
               placeholder="Введите название серии" 
               style="width: 100%; margin-bottom: 1.5rem;" 
               required>
        
        <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Описание серии
        </label>
        <textarea id="description" name="description" 
                  placeholder="Описание сюжета серии, общая концепция..." 
                  rows="6"
                  style="width: 100;"><?= e($series['description'] ?? $_POST['description'] ?? '') ?></textarea>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="submit" class="contrast">
            <?= $is_edit ? '💾 Сохранить изменения' : '📚 Создать серию' ?>
        </button>
        
        <a href="series.php" role="button" class="secondary">
            ❌ Отмена
        </a>
    </div>
</form>

<?php if ($is_edit): ?>
<div style="margin-top: 3rem;">
    <h3>Книги в этой серии</h3>
    
    <?php
    $bookModel = new Book($pdo);
    $books_in_series = $bookModel->findBySeries($series_id);
    
    // Вычисляем общую статистику
    $total_chapters = 0;
    $total_words = 0;
    foreach ($books_in_series as $book) {
        $stats = $bookModel->getBookStats($book['id']);
        $total_chapters += $stats['chapter_count'] ?? 0;
        $total_words += $stats['total_words'] ?? 0;
    }
    ?>
    
    <?php if (empty($books_in_series)): ?>
        <div style="text-align: center; padding: 2rem; background: #f9f9f9; border-radius: 5px;">
            <p>В этой серии пока нет книг.</p>
            <a href="books.php" class="adaptive-button">📚 Добавить книги</a>
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
                                <br><small style="color: #666;"><?= e(mb_strimwidth($book['description'], 0, 100, '...')) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($book['genre']) ?></td>
                        <td>
                            <span style="color: <?= $book['published'] ? 'green' : 'orange' ?>">
                                <?= $book['published'] ? '✅ Опубликована' : '📝 Черновик' ?>
                            </span>
                        </td>
                        <td>
                            <a href="book_edit.php?id=<?= $book['id'] ?>" class="compact-button secondary">
                                Редактировать
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1rem; padding: 0.5rem; background: #f5f5f5; border-radius: 3px;">
            <strong>Статистика серии:</strong> 
            Книг: <?= count($books_in_series) ?> | 
            Глав: <?= $total_chapters ?> |
            Слов: <?= $total_words ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include 'views/footer.php'; ?>