<?php
// views/chapters/create.php
include 'views/layouts/header.php';
?>

<h1>Новая глава для: <?= e($book['title']) ?></h1>

<?php if (isset($error) && $error): ?>
    <div class="alert alert-error">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<form method="post" id="chapter-form">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div style="margin-bottom: 1rem;">
        <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Название главы *
        </label>
        <input type="text" id="title" name="title" 
               value="<?= e($_POST['title'] ?? '') ?>" 
               placeholder="Введите название главы" 
               style="width: 100%; margin-bottom: 1.5rem;" 
               required>
        
        <label for="content" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Содержание главы *
        </label>
        
        <?php if (($book['editor_type'] ?? 'markdown') == 'html'): ?>
            <textarea id="content" name="content" class="html-editor" 
                      placeholder="Начните писать вашу главу..." 
                      rows="20"
                      style="width: 100%;"><?= e($_POST['content'] ?? '') ?></textarea>
        <?php else: ?>
            <div style="margin-bottom: 1rem; padding: 0.5rem; background: var(--card-background-color); border-radius: 5px;">
                <div style="display: flex; gap: 3px; flex-wrap: nowrap; overflow-x: auto; padding: 5px 0;">
                    <button type="button" onclick="insertMarkdown('**')" class="compact-button secondary" title="Жирный текст" style="white-space: nowrap; flex-shrink: 0;">**B**</button>
                    <button type="button" onclick="insertMarkdown('*')" class="compact-button secondary" title="Курсив" style="white-space: nowrap; flex-shrink: 0;">*I*</button>
                    <button type="button" onclick="insertMarkdown('~~')" class="compact-button secondary" title="Зачеркнутый" style="white-space: nowrap; flex-shrink: 0;">~~S~~</button>
                    <button type="button" onclick="insertMarkdown('`')" class="compact-button secondary" title="Код" style="white-space: nowrap; flex-shrink: 0;">`code`</button>
                    <button type="button" onclick="insertMarkdown('\n\n- ')" class="compact-button secondary" title="Список" style="white-space: nowrap; flex-shrink: 0;">- список</button>
                    <button type="button" onclick="insertMarkdown('\n\n> ')" class="compact-button secondary" title="Цитата" style="white-space: nowrap; flex-shrink: 0;">> цитата</button>
                    <button type="button" onclick="insertMarkdown('\n\n# ')" class="compact-button secondary" title="Заголовок" style="white-space: nowrap; flex-shrink: 0;"># Заголовок</button>
                    <button type="button" onclick="insertMarkdown('\n— ')" class="compact-button secondary" title="Диалог" style="white-space: nowrap; flex-shrink: 0;">— диалог</button>
                </div>
            </div>

            <textarea id="content" name="content" 
                      placeholder="Начните писать вашу главу... Поддерживается Markdown разметка." 
                      rows="20"
                      style="width: 100%; font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 14px; line-height: 1.5;"><?= e($_POST['content'] ?? '') ?></textarea>
            
            <div style="margin-top: 0.5rem; font-size: 0.9em; color: var(--muted-color);">
                <strong>Подсказка:</strong> Используйте Markdown для форматирования. 
                <a href="https://commonmark.org/help/" target="_blank">Справка по Markdown</a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 1rem;">
            <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Статус главы
            </label>
            <select id="status" name="status" style="width: 100%;">
                <option value="draft" <?= (($_POST['status'] ?? 'draft') == 'draft') ? 'selected' : '' ?>>📝 Черновик</option>
                <option value="published" <?= (($_POST['status'] ?? '') == 'published') ? 'selected' : '' ?>>✅ Опубликована</option>
            </select>
            <small style="color: var(--muted-color);">
                Опубликованные главы видны в публичном доступе
            </small>
        </div>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 1.5rem;">
        <button type="submit" class="contrast">
            💾 Сохранить главу
        </button>
        
        <button type="button" onclick="previewChapter()" class="secondary">
            👁️ Предпросмотр
        </button>
        
        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters" role="button" class="secondary">
            ❌ Отмена
        </a>
    </div>
</form>

<script>
function previewChapter() {
    const form = document.getElementById('chapter-form');
    const formData = new FormData(form);
    
    // Создаем временную форму для предпросмотра
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = '<?= SITE_URL ?>/chapters/preview';
    tempForm.target = '_blank';
    tempForm.style.display = 'none';
    
    // Добавляем CSRF токен
    const csrfInput = document.createElement('input');
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= generate_csrf_token() ?>';
    tempForm.appendChild(csrfInput);
    
    // Добавляем контент
    const contentInput = document.createElement('input');
    contentInput.name = 'content';
    contentInput.value = document.getElementById('content').value;
    tempForm.appendChild(contentInput);
    
    // Добавляем заголовок
    const titleInput = document.createElement('input');
    titleInput.name = 'title';
    titleInput.value = document.getElementById('title').value || 'Предпросмотр главы';
    tempForm.appendChild(titleInput);
    
    // Добавляем тип редактора
    const editorTypeInput = document.createElement('input');
    editorTypeInput.name = 'editor_type';
    editorTypeInput.value = '<?= $book['editor_type'] ?? 'markdown' ?>';
    tempForm.appendChild(editorTypeInput);
    
    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
}
</script>

<script src="/assets/js/markdown-editor.js"></script>
<script src="/assets/js/autosave.js"></script>
<?php include 'views/layouts/footer.php'; ?>