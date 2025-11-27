<?php
// views/series/create.php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Создание новой серии</h1>
                <a href="<?= SITE_URL ?>/series" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Назад к сериям
                </a>
            </div>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Название серии *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= e($_POST['title'] ?? '') ?>" 
                                   placeholder="Введите название серии" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="form-label">Описание серии</label>
                            <textarea class="form-control" id="description" name="description" 
                                      placeholder="Описание сюжета серии, общая концепция..." 
                                      rows="6"><?= e($_POST['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-collection"></i> Создать серию
                            </button>
                            
                            <a href="<?= SITE_URL ?>/series" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle"></i> Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Что такое серия?</h5>
                </div>
                <div class="card-body">
                    <p>Серия позволяет объединить несколько книг в одну тематическую коллекцию. Это полезно для:</p>
                    <ul>
                        <li>Циклов книг с общим сюжетом</li>
                        <li>Книг в одном мире или вселенной</li>
                        <li>Организации книг по темам или жанрам</li>
                    </ul>
                    <p>Вы сможете добавить книги в серию после её создания.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>