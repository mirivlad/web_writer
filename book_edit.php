<?php
require_once 'config/config.php';
require_login();

// Подключаем функции для обложек

$user_id = $_SESSION['user_id'];
$bookModel = new Book($pdo);

// Проверяем, редактируем ли существующую книгу
$book_id = $_GET['id'] ?? null;
$book = null;
$is_edit = false;

if ($book_id) {
    $book = $bookModel->findById($book_id);
    if (!$book || $book['user_id'] != $user_id) {
        $_SESSION['error'] = "Книга не найдена или у вас нет доступа";
        redirect('books.php');
    }
    $is_edit = true;
}

// Обработка формы
$cover_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Ошибка безопасности";
        redirect($is_edit ? "book_edit.php?id=$book_id" : 'book_edit.php');
    }
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    
    if (empty($title)) {
        $_SESSION['error'] = "Название книги обязательно";
    } else {
        $data = [
            'title' => $title,
            'description' => $description,
            'genre' => $genre,
            'user_id' => $user_id
        ];
        $data['published'] = isset($_POST['published']) ? 1 : 0;
        
        // Обработка загрузки обложки
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $cover_result = handleCoverUpload($_FILES['cover_image'], $book_id);
            if ($cover_result['success']) {
                $bookModel->updateCover($book_id, $cover_result['filename']);
                // Обновляем данные книги
                $book = $bookModel->findById($book_id);
            } else {
                $cover_error = $cover_result['error'];
            }
        }
        
        // Обработка удаления обложки
        if (isset($_POST['delete_cover']) && $_POST['delete_cover'] == '1') {
            $bookModel->deleteCover($book_id);
            $book = $bookModel->findById($book_id);
        }
        
        if ($is_edit) {
            $success = $bookModel->update($book_id, $data);
            $message = $success ? "Книга успешно обновлена" : "Ошибка при обновлении книги";
        } else {
            $success = $bookModel->create($data);
            $message = $success ? "Книга успешно создана" : "Ошибка при создании книги";
            
            if ($success) {
                $new_book_id = $pdo->lastInsertId();
                redirect("book_edit.php?id=$new_book_id");
            }
        }
        
        if ($success) {
            $_SESSION['success'] = $message;
            redirect('books.php');
        } else {
            $_SESSION['error'] = $message;
        }
    }
}

$page_title = $is_edit ? "Редактирование книги" : "Создание новой книги";
include 'views/header.php';
?>

<!-- Остальная часть формы остается той же, но добавляем поле обложки -->

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div style="max-width: 100%; margin-bottom: 0.5rem;">
        <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Название книги *
        </label>
        <input type="text" id="title" name="title" 
               value="<?= e($book['title'] ?? $_POST['title'] ?? '') ?>" 
               placeholder="Введите название книги" 
               style="width: 100%; margin-bottom: 1.5rem;" 
               required>
        
        <label for="genre" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Жанр
        </label>
        <input type="text" id="genre" name="genre" 
               value="<?= e($book['genre'] ?? $_POST['genre'] ?? '') ?>" 
               placeholder="Например: Фантастика, Роман, Детектив..."
               style="width: 100%; margin-bottom: 1.5rem;">
        
        <!-- ПОЛЕ ДЛЯ ОБЛОЖКИ -->
        <div style="margin-bottom: 1.5rem;">
            <label for="cover_image" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Обложка книги
            </label>
            
            <?php if (!empty($book['cover_image'])): ?>
                <div style="margin-bottom: 1rem;">
                    <p><strong>Текущая обложка:</strong></p>
                    <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                         alt="Обложка" 
                         style="max-width: 200px; height: auto; border-radius: 4px; border: 1px solid #ddd;">
                    <div style="margin-top: 0.5rem;">
                        <label style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="delete_cover" value="1">
                            Удалить обложку
                        </label>
                    </div>
                </div>
            <?php endif; ?>
            
            <input type="file" id="cover_image" name="cover_image" 
                   accept="image/jpeg, image/png, image/gif, image/webp"
                   style="height: 2.6rem;">
            <small style="color: #666;">
                Разрешены: JPG, PNG, GIF, WebP. Максимальный размер: 5MB.
                Рекомендуемый размер: 300×450 пикселей.
            </small>
            
            <?php if (!empty($cover_error)): ?>
                <div style="color: #d32f2f; margin-top: 0.5rem;">
                    ❌ <?= e($cover_error) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Описание книги
        </label>
        <textarea id="description" name="description" 
                  placeholder="Краткое описание сюжета или аннотация..." 
                  rows="6"
                  style="width: 100%;"><?= e($book['description'] ?? $_POST['description'] ?? '') ?></textarea>
        
        <div>
            <label for="published">
                <input type="checkbox" id="published" name="published" value="1"
                <?= !empty($book['published']) || (!empty($_POST['published']) && $_POST['published']) ? 'checked' : '' ?>>
                Опубликовать книгу (показывать на публичной странице автора)
            </label>
        </div>
    </div>
    
    <div style="display: flex; gap: 5px; flex-wrap: wrap; align-items: center;">
        <button type="submit" class="contrast compact-button">
            <?= $is_edit ? '💾 Сохранить изменения' : '📖 Создать книгу' ?>
        </button>
    </div>
</form>
<?php if ($is_edit): ?>
<form method="post" action="book_delete.php" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить книгу «<?= e($book['title']) ?>»? Все главы также будут удалены.');">
    <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <button type="submit" class="compact-button secondary" style="background: #ff4444; border-color: #ff4444; color: white;" title="Удалить книгу">
        🗑️
    </button>
</form>
<?php endif ?>
<?php if ($is_edit): ?>
<div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
    <h3>Публичная ссылка для чтения</h3>
    <p style="margin-bottom: 0.5rem;">Отправьте эту ссылку читателям для просмотра опубликованных глав:</p>
    
    <div style="display: flex; gap: 5px; align-items: center; flex-wrap: wrap;">
        <input type="text" 
               id="share-link" 
               value="<?= e(SITE_URL . '/view_book.php?share_token=' . $book['share_token']) ?>" 
               readonly 
               style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: white;">
        
        <button type="button" onclick="copyShareLink()" class="compact-button secondary">
            📋 Копировать
        </button>
        
        <form method="post" action="book_regenerate_token.php" style="display: inline;">
            <input type="hidden" name="book_id" value="<?= $book_id ?>">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <button type="submit" class="compact-button secondary" onclick="return confirm('Создать новую ссылку? Старая ссылка перестанет работать.')">
                🔄 Обновить
            </button>
        </form>
    </div>
    
    <p style="margin-top: 0.5rem; font-size: 0.9em; color: #666;">
        <strong>Примечание:</strong> В публичном просмотре отображаются только главы со статусом "Опубликована"
    </p>
</div>

<script>
function copyShareLink() {
    const shareLink = document.getElementById('share-link');
    shareLink.select();
    shareLink.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Показать уведомление
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '✅ Скопировано';
    setTimeout(() => {
        button.innerHTML = originalText;
    }, 2000);
}
</script>
<?php endif; ?>

<?php if ($is_edit): ?>
<div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
    <h3>Экспорт книги</h3>
    <p style="margin-bottom: 0.5rem;">Экспортируйте книгу в различные форматы:</p>
    
    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
        <a href="export_book.php?book_id=<?= $book_id ?>&format=pdf" class="adaptive-button secondary" target="_blank">
            📄 PDF
        </a>
        <a href="export_book.php?book_id=<?= $book_id ?>&format=docx" class="adaptive-button secondary" target="_blank">
            📝 DOCX
        </a>
        <a href="export_book.php?book_id=<?= $book_id ?>&format=odt" class="adaptive-button secondary" target="_blank">
            📄 ODT
        </a>
        <a href="export_book.php?book_id=<?= $book_id ?>&format=html" class="adaptive-button secondary" target="_blank">
            🌐 HTML
        </a>
        <a href="export_book.php?book_id=<?= $book_id ?>&format=txt" class="adaptive-button secondary" target="_blank">
            📄 TXT
        </a>
    </div>
    
    <p style="margin-top: 0.5rem; font-size: 0.9em; color: #666;">
        <strong>Примечание:</strong> Экспортируются все главы книги (включая черновики)
    </p>
</div>
<?php endif; ?>

<?php if ($is_edit): ?>
<div style="margin-top: 3rem;">
    <h2>Главы этой книги</h2>
        <a href="chapters.php?book_id=<?= $book_id ?>" class="compact-button secondary">
                📑 Все главы
        </a>
        &nbsp;
        <a href="chapter_edit.php?book_id=<?= $book_id ?>" role="button" class="compact-button secondary">
                ✏️ Добавить главу
        </a>
    <?php
    // Получаем главы книги
    $stmt = $pdo->prepare("SELECT * FROM chapters WHERE book_id = ? ORDER BY sort_order, created_at");
    $stmt->execute([$book_id]);
    $chapters = $stmt->fetchAll();
    
    if ($chapters): ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 12px 8px;">Название</th>
                        <th style="text-align: left; padding: 12px 8px;">Статус</th>
                        <th style="text-align: left; padding: 12px 8px;">Слов</th>
                        <th style="text-align: left; padding: 12px 8px;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chapters as $chapter): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px 8px;"><?= e($chapter['title']) ?></td>
                        <td style="padding: 12px 8px;">
                            <span style="color: <?= $chapter['status'] == 'published' ? 'green' : 'orange' ?>">
                                <?= $chapter['status'] == 'published' ? 'Опубликована' : 'Черновик' ?>
                            </span>
                        </td>
                        <td style="padding: 12px 8px;"><?= $chapter['word_count'] ?></td>
                        <td style="padding: 12px 8px;">
                            <a href="chapter_edit.php?id=<?= $chapter['id'] ?>" role="button" class="compact-button secondary" style="text-decoration: none;">
                                Редактировать
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 2rem; background: #f9f9f9; border-radius: 5px;">
            <p style="margin-bottom: 1rem;">В этой книге пока нет глав.</p>
            <a href="chapter_edit.php?book_id=<?= $book_id ?>" role="button" class="compact-button secondary" >
                ✏️ Добавить первую главу
            </a>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include 'views/footer.php'; ?>