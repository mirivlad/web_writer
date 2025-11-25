<?php
// views/books/edit.php
include 'views/layouts/header.php';
?>
<?php if (isset($_SESSION['cover_error'])): ?>
    <div class="alert alert-error">
        Ошибка загрузки обложки: <?= e($_SESSION['cover_error']) ?>
        <?php unset($_SESSION['cover_error']); ?>
    </div>
<?php endif; ?>
<h1>Редактирование книги</h1>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <div style="max-width: 100%; margin-bottom: 0.5rem;">
        <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Название книги *
        </label>
        <input type="text" id="title" name="title" 
               value="<?= e($book['title'] ?? '') ?>" 
               placeholder="Введите название книги" 
               style="width: 100%; margin-bottom: 1.5rem;" 
               required>
        <label for="genre" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Жанр
        </label>
        <input type="text" id="genre" name="genre" 
               value="<?= e($book['genre'] ?? '') ?>" 
               placeholder="Например: Фантастика, Роман, Детектив..."
               style="width: 100%; margin-bottom: 1.5rem;">
        <label for="series_id" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Серия
        </label>
        <select id="series_id" name="series_id" style="width: 100%; margin-bottom: 1rem;">
            <option value="">-- Без серии --</option>
            <?php foreach ($series as $ser): ?>
                <option value="<?= $ser['id'] ?>" <?= ($ser['id'] == ($book['series_id'] ?? 0)) ? 'selected' : '' ?>>
                    <?= e($ser['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="sort_order_in_series" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Порядок в серии
        </label>
        <input type="number" id="sort_order_in_series" name="sort_order_in_series" 
            value="<?= e($book['sort_order_in_series'] ?? '') ?>" 
            placeholder="Номер по порядку в серии"
            min="1"
            style="width: 100%; margin-bottom: 1.5rem;">
        <!-- Обложка -->
        <div style="margin-bottom: 1.5rem;">
            <label for="cover_image" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                Обложка книги
            </label>
            <?php if (!empty($book['cover_image'])): ?>
                <div style="margin-bottom: 1rem;">
                    <p><strong>Текущая обложка:</strong></p>
                    <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                         alt="Обложка" 
                         style="max-width: 200px; height: auto; border-radius: 4px; border: 1px solid var(--border-color);">
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
            <small style="color: var(--muted-color);">
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
                  style="width: 100%;"><?= e($book['description'] ?? '') ?></textarea>
        <div style="margin-top: 1rem;">
            <label for="published">
                <input type="checkbox" id="published" name="published" value="1"
                <?= !empty($book['published']) ? 'checked' : '' ?>>
                Опубликовать книгу (показывать на публичной странице автора)
            </label>
        </div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 1.5rem;">
        <button type="submit" class="contrast">
            💾 Сохранить изменения
        </button>
        <a href="<?= SITE_URL ?>/books" role="button" class="secondary">
            ❌ Отмена
        </a>
    </div>
</form>

<?php if ($book): ?>
    <div style="margin-top: 2rem; padding: 1rem; background: var(--card-background-color); border-radius: 5px;">
        <h3>Публичная ссылка для чтения</h3>
        <div style="display: flex; gap: 5px; align-items: center; flex-wrap: wrap;">
            <input type="text" 
                   id="share-link" 
                   value="<?= e(SITE_URL . '/book/' . $book['share_token']) ?>" 
                   readonly 
                   style="flex: 1; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; background: white;">
            <button type="button" onclick="copyShareLink()" class="compact-button secondary">
                📋 Копировать
            </button>
            <form method="post" action="<?= SITE_URL ?>/books/<?= $book['id'] ?>/regenerate-token" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <button type="submit" class="compact-button secondary" onclick="return confirm('Создать новую ссылку? Старая ссылка перестанет работать.')">
                    🔄 Обновить
                </button>
            </form>
        </div>
        <p style="margin-top: 0.5rem; font-size: 0.8em; color: var(--muted-color);">
            <strong>Примечание:</strong> В публичном просмотре отображаются только главы со статусом "Опубликована"
        </p>
    </div>
    <div style="margin-top: 2rem; padding: 1rem; background: var(--card-background-color); border-radius: 5px;">
        <h3>Экспорт книги</h3>
        <p style="margin-bottom: 0.5rem;">Экспортируйте книгу в различные форматы:</p>
        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
            <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/pdf" class="adaptive-button secondary" target="_blank">
                📄 PDF
            </a>
            <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/docx" class="adaptive-button secondary" target="_blank">
                📝 DOCX
            </a>
            <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/html" class="adaptive-button secondary" target="_blank">
                🌐 HTML
            </a>
            <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/txt" class="adaptive-button secondary" target="_blank">
                📄 TXT
            </a>
        </div>
        <p style="margin-top: 0.5rem; font-size: 0.9em; color: var(--muted-color);">
            <strong>Примечание:</strong> Экспортируются все главы книги (включая черновики)
        </p>
    </div>
    <div style="margin-top: 3rem;">
        <h2>Главы этой книги</h2>
        <div style="display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 1rem;">
            <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters" class="adaptive-button secondary">
                📑 Все главы
            </a>
            <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters/create" class="adaptive-button secondary">
                ✏️ Добавить главу
            </a>
        </div>
        <?php if (!empty($chapters)): ?>
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
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 12px 8px;"><?= e($chapter['title']) ?></td>
                            <td style="padding: 12px 8px;">
                                <span style="color: <?= $chapter['status'] == 'published' ? 'green' : 'orange' ?>">
                                    <?= $chapter['status'] == 'published' ? 'Опубликована' : 'Черновик' ?>
                                </span>
                            </td>
                            <td style="padding: 12px 8px;"><?= $chapter['word_count'] ?></td>
                            <td style="padding: 12px 8px;">
                                <a href="<?= SITE_URL ?>/chapters/<?= $chapter['id'] ?>/edit" class="compact-button secondary">
                                    Редактировать
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; background: var(--card-background-color); border-radius: 5px;">
                <p style="margin-bottom: 1rem;">В этой книге пока нет глав.</p>
                <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters/create" class="adaptive-button secondary">
                    ✏️ Добавить первую главу
                </a>
            </div>
        <?php endif; ?>
    </div>
    <div style="margin-top: 2rem; text-align: center;">
        <form method="post" action="<?= SITE_URL ?>/books/<?= $book['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить книгу «<?= e($book['title']) ?>»? Все главы также будут удалены.');">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <button type="submit" class="button" style="background: #ff4444; border-color: #ff4444; color: white;">
                🗑️ Удалить книгу
            </button>
        </form>
    </div>
<?php endif; ?>

<script>


document.addEventListener('DOMContentLoaded', function() {
    
    // Копирование ссылки для чтения
    window.copyShareLink = function() {
        const shareLink = document.getElementById('share-link');
        shareLink.select();
        document.execCommand('copy');
        const button = event.target;
        const originalText = '📋 Копировать';
        button.textContent = '✅ Скопировано';
        setTimeout(() => {
            button.textContent = originalText;
        }, 2000);
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>