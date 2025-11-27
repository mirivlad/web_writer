<?php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Новая глава для: <?= e($book['title']) ?></h1>
        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к главам
        </a>
    </div>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" id="chapter-form">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="mb-3">
                    <label for="title" class="form-label">Название главы *</label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?= e($_POST['title'] ?? '') ?>" 
                           placeholder="Введите название главы" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Содержание главы *</label>
                    <!-- Контейнер Quill -->
                    <div id="quill-editor"
                         class="writer-editor-container"
                         style="height:500px;"
                         data-content="<?= htmlspecialchars($_POST['content'] ?? '', ENT_QUOTES) ?>">
                    </div>
                    <!-- Скрытый textarea для формы -->
                    <textarea id="content" name="content" style="display:none;"><?= e($_POST['content'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="status" class="form-label">Статус главы</label>
                    <select class="form-select" id="status" name="status">
                        <option value="draft" <?= (($_POST['status'] ?? 'draft') == 'draft') ? 'selected' : '' ?>>📝 Черновик</option>
                        <option value="published" <?= (($_POST['status'] ?? '') == 'published') ? 'selected' : '' ?>>✅ Опубликована</option>
                    </select>
                    <div class="form-text">
                        Опубликованные главы видны в публичном доступе
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Сохранить главу
                    </button>
                    <button type="button" onclick="previewChapter()" class="btn btn-outline-secondary">
                        <i class="bi bi-eye"></i> Предпросмотр
                    </button>
                    <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- <link href="/assets/css/quill_reset.css" rel="stylesheet"> -->
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
<?php include 'views/layouts/footer.php'; ?>