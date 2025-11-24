<?php
// views/series/create.php
include 'views/layouts/header.php';
?>

<h1>Создание новой серии</h1>

<?php if (isset($error) && $error): ?>
    <div class="alert alert-error">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div style="max-width: 100%; margin-bottom: 1rem;">
        <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Название серии *
        </label>
        <input type="text" id="title" name="title" 
               value="<?= e($_POST['title'] ?? '') ?>" 
               placeholder="Введите название серии" 
               style="width: 100%; margin-bottom: 1.5rem;" 
               required>
        
        <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
            Описание серии
        </label>
        <textarea id="description" name="description" 
                  placeholder="Описание сюжета серии, общая концепция..." 
                  rows="6"
                  style="width: 100%;"><?= e($_POST['description'] ?? '') ?></textarea>
    </div>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="submit" class="contrast">
            📚 Создать серию
        </button>
        
        <a href="<?= SITE_URL ?>/series" role="button" class="secondary">
            ❌ Отмена
        </a>
    </div>
</form>

<div style="margin-top: 2rem; padding: 1rem; background: var(--card-background-color); border-radius: 5px;">
    <h3>Что такое серия?</h3>
    <p>Серия позволяет объединить несколько книг в одну тематическую коллекцию. Это полезно для:</p>
    <ul>
        <li>Циклов книг с общим сюжетом</li>
        <li>Книг в одном мире или вселенной</li>
        <li>Организации книг по темам или жанрам</li>
    </ul>
    <p>Вы сможете добавить книги в серию после её создания.</p>
</div>

<?php include 'views/layouts/footer.php'; ?>