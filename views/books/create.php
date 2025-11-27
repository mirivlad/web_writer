<?php
// views/books/create.php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2">Создание новой книги</h1>
                <a href="<?= SITE_URL ?>/books" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Назад к книгам
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= e($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($cover_error) && $cover_error): ?>
                <div class="alert alert-danger">
                    Ошибка загрузки обложки: <?= e($cover_error) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Название книги *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= e($_POST['title'] ?? '') ?>" 
                                   placeholder="Введите название книги" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="genre" class="form-label">Жанр</label>
                                    <input type="text" class="form-control" id="genre" name="genre" 
                                           value="<?= e($_POST['genre'] ?? '') ?>" 
                                           placeholder="Например: Фантастика, Роман, Детектив...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="series_id" class="form-label">Серия</label>
                                    <select class="form-select" id="series_id" name="series_id">
                                        <option value="">-- Без серии --</option>
                                        <?php foreach ($series as $ser): ?>
                                            <option value="<?= $ser['id'] ?>" <?= (($_POST['series_id'] ?? '') == $ser['id']) ? 'selected' : '' ?>>
                                                <?= e($ser['title']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание книги</label>
                            <textarea class="form-control" id="description" name="description" 
                                      placeholder="Краткое описание сюжета или аннотация..." 
                                      rows="4"><?= e($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="cover_image" class="form-label">Обложка книги</label>
                            <input type="file" class="form-control" id="cover_image" name="cover_image" 
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="form-text">
                                Разрешены форматы: JPG, PNG, GIF, WebP. Максимальный размер: 5MB.
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="published" name="published" value="1"
                                    <?= (!empty($_POST['published']) && $_POST['published']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="published">
                                    Опубликовать книгу (показывать на публичной странице автора)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-journal-plus"></i> Создать книгу
                            </button>
                            <a href="<?= SITE_URL ?>/books" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>