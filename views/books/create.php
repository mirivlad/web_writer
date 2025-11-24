<?php
// views/books/create.php
include 'views/layouts/header.php';
?>
<h1>Создание новой книги</h1>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <div style="max-width: 100%; margin-bottom: 0.5rem;">
        <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Название книги *
        </label>
        <input type="text" id="title" name="title" 
               value="<?= e($_POST['title'] ?? '') ?>" 
               placeholder="Введите название книги" 
               style="width: 100%; margin-bottom: 1.5rem;" 
               required>
        <label for="genre" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Жанр
        </label>
        <input type="text" id="genre" name="genre" 
               value="<?= e($_POST['genre'] ?? '') ?>" 
               placeholder="Например: Фантастика, Роман, Детектив..."
               style="width: 100%; margin-bottom: 1.5rem;">
        <label for="editor_type" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Режим редактора
        </label>
        <select id="editor_type" name="editor_type" style="width: 100%; margin-bottom: 1.5rem;">
            <option value="markdown" <?= ($_POST['editor_type'] ?? 'markdown') == 'markdown' ? 'selected' : '' ?>>Markdown редактор</option>
            <option value="html" <?= ($_POST['editor_type'] ?? '') == 'html' ? 'selected' : '' ?>>HTML редактор (TinyMCE)</option>
        </select>
        <label for="series_id" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Серия
        </label>
        <select id="series_id" name="series_id" style="width: 100%; margin-bottom: 1rem;">
            <option value="">-- Без серии --</option>
            <?php foreach ($series as $ser): ?>
                <option value="<?= $ser['id'] ?>" <?= (($_POST['series_id'] ?? '') == $ser['id']) ? 'selected' : '' ?>>
                    <?= e($ser['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Описание книги
        </label>
        <textarea id="description" name="description" 
                  placeholder="Краткое описание сюжета или аннотация..." 
                  rows="6"
                  style="width: 100;"><?= e($_POST['description'] ?? '') ?></textarea>
        <div style="margin-top: 1rem;">
            <label for="published">
                <input type="checkbox" id="published" name="published" value="1"
                <?= (!empty($_POST['published']) && $_POST['published']) ? 'checked' : '' ?>>
                Опубликовать книгу (показывать на публичной странице автора)
            </label>
        </div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 1.5rem;">
        <button type="submit" class="contrast">
            📖 Создать книгу
        </button>
        <a href="<?= SITE_URL ?>/books" role="button" class="secondary">
            ❌ Отмена
        </a>
    </div>
</form>
<?php include 'views/layouts/footer.php'; ?>