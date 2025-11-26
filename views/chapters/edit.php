<?php
// views/chapters/edit.php
include 'views/layouts/header.php';
?>

<h1>Редактирование главы: <?= e($chapter['title']) ?></h1>

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
               value="<?= e($chapter['title']) ?>" 
               placeholder="Введите название главы" 
               style="width: 100%; margin-bottom: 1.5rem;" 
               required>
        
        <label for="content" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Содержание главы *
        </label>
        <!-- Контейнер Quill -->
        <div id="quill-editor"
             class="writer-editor-container"
             style="height:500px;"
             data-content="<?= htmlspecialchars($chapter['content'] ?? '', ENT_QUOTES) ?>">
        </div>

        <!-- Скрытый textarea для формы -->
        <textarea id="content" name="content" style="display:none;"></textarea>
            
        
        <div style="margin-top: 1rem;">
            <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Статус главы
            </label>
            <select id="status" name="status" style="width: 100%;">
                <option value="draft" <?= ($chapter['status'] == 'draft') ? 'selected' : '' ?>>📝 Черновик</option>
                <option value="published" <?= ($chapter['status'] == 'published') ? 'selected' : '' ?>>✅ Опубликована</option>
            </select>
            <small style="color: var(--muted-color);">
                Опубликованные главы видны в публичном доступе
            </small>
        </div>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 1.5rem;">
        <button type="submit" class="contrast">
            💾 Сохранить изменения
        </button>
        
        <button type="button" onclick="previewChapter()" class="secondary">
            👁️ Предпросмотр
        </button>
        
        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters" role="button" class="secondary">
            ❌ Отмена
        </a>
    </div>
</form>

<div style="margin-top: 2rem; padding: 1rem; background: var(--card-background-color); border-radius: 5px;">
    <h3>Информация о главе</h3>
    <p><strong>Книга:</strong> <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit"><?= e($book['title']) ?></a></p>
    <p><strong>Количество слов:</strong> <?= $chapter['word_count'] ?></p>
    <p><strong>Создана:</strong> <?= date('d.m.Y H:i', strtotime($chapter['created_at'])) ?></p>
    <p><strong>Обновлена:</strong> <?= date('d.m.Y H:i', strtotime($chapter['updated_at'])) ?></p>
</div>
<link href="/assets/css/quill_reset.css" rel="stylesheet">
<script>
function previewChapter() {
    const form = document.getElementById('chapter-form');
    const formData = new FormData(form);
    
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = '<?= SITE_URL ?>/chapters/preview';
    tempForm.target = '_blank';
    tempForm.style.display = 'none';
    
    const csrfInput = document.createElement('input');
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= generate_csrf_token() ?>';
    tempForm.appendChild(csrfInput);
    
    const contentInput = document.createElement('input');
    contentInput.name = 'content';
    contentInput.value = document.getElementById('content').value;
    tempForm.appendChild(contentInput);
    
    const titleInput = document.createElement('input');
    titleInput.name = 'title';
    titleInput.value = document.getElementById('title').value || 'Предпросмотр главы';
    tempForm.appendChild(titleInput);
    
    const editorTypeInput = document.createElement('input');
    editorTypeInput.name = 'editor_type';
    editorTypeInput.value = '<?= $book['editor_type'] ?? 'markdown' ?>';
    tempForm.appendChild(editorTypeInput);
    
    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
}
</script>
<script src="/assets/js/editor.js"></script>
<script src="/assets/js/autosave.js"></script>
<?php include 'views/layouts/footer.php'; ?>