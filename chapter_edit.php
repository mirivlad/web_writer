<?php
require_once 'config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$chapterModel = new Chapter($pdo);
$bookModel = new Book($pdo);

// Получаем book_id из GET или из существующей главы
$chapter_id = $_GET['id'] ?? null;
$book_id = $_GET['book_id'] ?? null;
$chapter = null;
$is_edit = false;

// Если редактируем существующую главу
if ($chapter_id) {
    $chapter = $chapterModel->findById($chapter_id);
    if (!$chapter || $chapter['user_id'] != $user_id) {
        $_SESSION['error'] = "Глава не найдена или у вас нет доступа";
        redirect('books.php');
    }
    $book_id = $chapter['book_id'];
    $is_edit = true;
}

if (!$book_id) {
    $_SESSION['error'] = "Не указана книга";
    redirect('books.php');
}

if (!$bookModel->userOwnsBook($book_id, $user_id)) {
    $_SESSION['error'] = "У вас нет доступа к этой книге";
    redirect('books.php');
}

// Получаем информацию о книге
$book = $bookModel->findById($book_id);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Ошибка безопасности";
        redirect($is_edit ? "chapter_edit.php?id=$chapter_id" : "chapter_edit.php?book_id=$book_id");
    }
    
    // Обработка автосохранения
    if (isset($_POST['autosave']) && $_POST['autosave'] === 'true') {
        // Автосохранение работает только для существующих глав
        // Если это не редактирование, игнорируем автосохранение
        if (!$is_edit) {
            
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Автосохранение недоступно для новых глав']);
            exit;
        }
        
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        
        if (empty($title)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Название главы обязательно']);
            exit;
        }
        
        $data = [
            'title' => $title,
            'content' => $content,
            'status' => $status,
            'book_id' => $book_id
        ];
        
        $success = $chapterModel->update($chapter_id, $data);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }
    
    // Обычная обработка формы (не автосохранение)
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    
    if (empty($title)) {
        $_SESSION['error'] = "Название главы обязательно";
    } else {
        $data = [
            'title' => $title,
            'content' => $content,
            'status' => $status,
            'book_id' => $book_id
        ];
        
        if ($is_edit) {
            $success = $chapterModel->update($chapter_id, $data);
            $message = $success ? "Глава успешно обновлена" : "Ошибка при обновлении главы";
        } else {
            $success = $chapterModel->create($data);
            $message = $success ? "Глава успешно создана" : "Ошибка при создании главы";
            
            if ($success) {
                $new_chapter_id = $pdo->lastInsertId();
                redirect("chapter_edit.php?id=$new_chapter_id");
            }
        }
        
        if ($success) {
            $_SESSION['success'] = $message;
            redirect("book_edit.php?id=$book_id");
        } else {
            $_SESSION['error'] = $message;
        }
    }
}

$page_title = $is_edit ? "Редактирование главы" : "Создание новой главы";
include 'views/header.php';
?>
<?php if ($is_edit): ?>
<div style="margin-top: 1rem;">
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php
        // Получаем все главы книги для навигации
        $chapters = $chapterModel->findByBook($book_id);
        $current_index = null;
        
        // Находим индекс текущей главы
        foreach ($chapters as $index => $chap) {
            if ($chap['id'] == $chapter_id) {
                $current_index = $index;
                break;
            }
        }
        
        if ($current_index !== null && $current_index > 0): 
            $prev_chapter = $chapters[$current_index - 1];
        ?>
            <a href="chapter_edit.php?id=<?= $prev_chapter['id'] ?>" role="button" class="secondary" style="padding: 2px 4px;">
                ⬅️ Предыдущая: <?= e(mb_strimwidth($prev_chapter['title'], 0, 30, '...')) ?>
            </a>
        <?php endif; ?>
        
        <?php if ($current_index !== null && $current_index < count($chapters) - 1): 
            $next_chapter = $chapters[$current_index + 1];
        ?>
            <a href="chapter_edit.php?id=<?= $next_chapter['id'] ?>" role="button" class="secondary" style="padding: 2px 4px;">
                Следующая: <?= e(mb_strimwidth($next_chapter['title'], 0, 30, '...')) ?> ➡️
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<h1><?= $is_edit ? "Редактирование главы" : "Создание новой главы" ?></h1>
<p><strong>Книга:</strong> <?= e($book['title']) ?></p>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?= e($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<form method="post" id="main-form">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div style="max-width: 100%; margin-bottom: 1rem;">
        <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Название главы *
        </label>
        <input type="text" id="title" name="title" 
               value="<?= e($chapter['title'] ?? $_POST['title'] ?? '') ?>" 
               placeholder="Введите название главы" 
               style="width: 100%; margin-bottom: 1.5rem;" 
               required>
        
        <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Статус
        </label>
        <select id="status" name="status" style="width: 100%; margin-bottom: 1.5rem;">
            <option value="draft" <?= ($chapter['status'] ?? 'draft') == 'draft' ? 'selected' : '' ?>>Черновик</option>
            <option value="published" <?= ($chapter['status'] ?? '') == 'published' ? 'selected' : '' ?>>Опубликована</option>
        </select>
        
        <label for="content" style="display: block; margin-bottom: 0; font-weight: bold;">
            Содержание главы
            <?php if (isset($book['editor_type'])): ?>
                <small style="color: #666; font-weight: normal;">
                    (Режим: <?= $book['editor_type'] == 'markdown' ? 'Markdown' : 'HTML' ?>)
                </small>
            <?php endif; ?>
        </label>

        <?php if (($book['editor_type'] ?? 'markdown') === 'html'): ?>
            <!-- HTML редактор (TinyMCE) -->
            <textarea name="content" id="content" style="width: 100%; min-height: 500px;">
                <?= e($chapter['content'] ?? $_POST['content'] ?? '') ?>
            </textarea>
            
            <!-- Подключаем TinyMCE -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.6/tinymce.min.js" referrerpolicy="origin"></script>
            <script>
                tinymce.init({
                    selector: '#content',
                    plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table emoticons',
                    toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | code preview fullscreen',
                    menubar: 'edit view insert format tools table',
                    height: 500,
                    language: 'ru',
                    branding: false,
                    promotion: false,
                    image_advtab: true,
                    
                    // Важные настройки для сохранения структуры
                    forced_root_block: 'p', // Используем <p> вместо <div>
                    force_br_newlines: false, // Не использовать <br> вместо абзацев
                    force_p_newlines: true, // Всегда создавать новые абзацы при Enter
                    convert_newlines_to_brs: false, // Не конвертировать переносы в <br>
                    remove_trailing_brs: true, // Убирать лишние <br> в конце
                    
                    // Настройки форматирования
                    formats: {
                        // Сохраняем семантическое форматирование
                        bold: { inline: 'strong' },
                        italic: { inline: 'em' },
                        underline: { inline: 'u', exact: true },
                        strikethrough: { inline: 'del' }
                    },
                    
                    // Настройки контента
                    content_style: `
                        body { 
                            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
                            font-size: 14px; 
                            line-height: 1.6;
                            margin: 0;
                            padding: 10px;
                        }
                        p { 
                            margin: 0 0 1em 0;
                        }
                        h1, h2, h3, h4, h5, h6 {
                            margin: 1em 0 0.5em 0;
                        }
                    `,
                    
                    // Настройки для чистого HTML
                    valid_elements: '*[*]', // Разрешаем все элементы (можно ограничить при необходимости)
                    valid_children: '+body[p,div,h1,h2,h3,h4,h5,h6,blockquote,pre,ul,ol,li,table]',
                    
                    // Автосохранение
                    setup: function (editor) {
                        editor.on('init', function () {
                            // Нормализуем контент при инициализации
                            var content = editor.getContent();
                            if (content && !content.match(/<p[^>]*>/) && content.trim().length > 0) {
                                // Если нет тегов абзацев, оборачиваем в <p>
                                editor.setContent('<p>' + content.replace(/\n/g, '</p><p>') + '</p>');
                            }
                        });
                        
                        editor.on('keydown', function (e) {
                            clearTimeout(window.tinymceSaveTimeout);
                            window.tinymceSaveTimeout = setTimeout(function() {
                                if (typeof autoSave === 'function') {
                                    autoSave();
                                }
                            }, 2000);
                        });
                        
                        // Обработка вставки текста
                        editor.on('paste', function (e) {
                            // Нормализуем вставленный текст
                            setTimeout(function() {
                                var content = editor.getContent();
                                // Убеждаемся, что контент имеет правильную структуру абзацев
                                editor.setContent(content);
                            }, 100);
                        });
                    }
                });
                </script>
        <?php else: ?>
            <!-- Markdown редактор (существующий) -->
            <textarea name="content" id="content"
                    placeholder="Начните писать вашу главу здесь..." 
                    rows="15"
                    style="width: 100%; font-family: monospace;"><?= e($chapter['content'] ?? $_POST['content'] ?? '') ?></textarea>
            
            <script src="/assets/js/markdown-editor.js"></script>
            <?php if ($is_edit): ?>
                <script src="/assets/js/autosave.js"></script>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</form>

<div class="button-group">
    <button type="submit" form="main-form" class="contrast">
        <?= $is_edit ? '💾 Сохранить изменения' : '📝 Создать главу' ?>
    </button>
    
    <a href="book_edit.php?id=<?= $book_id ?>" role="button" class="secondary">
        ❌ Отмена
    </a>
    
    <button type="button" class="green-btn" id="preview-button">
        👁️ Предпросмотр
    </button>
</div>

<!-- Форма для предпросмотра -->
<form method="post" action="preview.php" target="_blank" id="preview-form" style="display: none;">
    <input type="hidden" name="content" id="preview-content">
    <input type="hidden" name="title" id="preview-title" value="<?= e($chapter['title'] ?? 'Новая глава') ?>">
    <input type="hidden" name="editor_type" id="preview-editor-type" value="<?= e($book['editor_type'] ?? 'markdown') ?>">
</form>

<?php if ($is_edit): ?>
<div class="button-group">
    <a href="chapter_edit.php?book_id=<?= $book_id ?>" role="button">
        ➕ Новая глава
    </a>
    
    <form method="post" action="chapter_delete.php" style="flex: 1;" onsubmit="return confirm('Вы уверены, что хотите удалить эту главу? Это действие нельзя отменить.');">
        <input type="hidden" name="chapter_id" value="<?= $chapter_id ?>">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <button type="submit" class="secondary delete-btn">
            🗑️ Удалить
        </button>
    </form>
</div>
<?php endif; ?>

<?php if ($is_edit): ?>
<div style="margin-top: 3rem;">
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php
        // Получаем все главы книги для навигации
        $chapters = $chapterModel->findByBook($book_id);
        $current_index = null;
        
        // Находим индекс текущей главы
        foreach ($chapters as $index => $chap) {
            if ($chap['id'] == $chapter_id) {
                $current_index = $index;
                break;
            }
        }
        
        if ($current_index !== null && $current_index > 0): 
            $prev_chapter = $chapters[$current_index - 1];
        ?>
            <a href="chapter_edit.php?id=<?= $prev_chapter['id'] ?>" role="button" class="secondary" style="padding: 2px 4px;">
                ⬅️ Предыдущая: <?= e(mb_strimwidth($prev_chapter['title'], 0, 30, '...')) ?>
            </a>
        <?php endif; ?>
        
        <?php if ($current_index !== null && $current_index < count($chapters) - 1): 
            $next_chapter = $chapters[$current_index + 1];
        ?>
            <a href="chapter_edit.php?id=<?= $next_chapter['id'] ?>" role="button" class="secondary" style="padding: 2px 4px;">
                Следующая: <?= e(mb_strimwidth($next_chapter['title'], 0, 30, '...')) ?> ➡️
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
// Обработчик для кнопки предпросмотра
document.getElementById('preview-button').addEventListener('click', function() {
    // Обновляем содержимое для предпросмотра
    document.getElementById('preview-content').value = document.getElementById('content').value;
    document.getElementById('preview-title').value = document.getElementById('title').value || 'Новая глава';
    
    // Отправляем форму предпросмотра
    document.getElementById('preview-form').submit();
});
</script>



<?php include 'views/footer.php'; ?>