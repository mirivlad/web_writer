<?php
// views/books/edit.php
include 'views/layouts/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Редактирование книги</h1>
        <a href="<?= SITE_URL ?>/books" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к книгам
        </a>
    </div>

    <?php if (isset($_SESSION['cover_error'])): ?>
        <div class="alert alert-danger">
            Ошибка загрузки обложки: <?= e($_SESSION['cover_error']) ?>
            <?php unset($_SESSION['cover_error']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Основная информация</h5>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Название книги *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= e($book['title'] ?? '') ?>" 
                                   placeholder="Введите название книги" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="genre" class="form-label">Жанр</label>
                                    <input type="text" class="form-control" id="genre" name="genre" 
                                           value="<?= e($book['genre'] ?? '') ?>" 
                                           placeholder="Например: Фантастика, Роман, Детектив...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="series_id" class="form-label">Серия</label>
                                    <select class="form-select" id="series_id" name="series_id">
                                        <option value="">-- Без серии --</option>
                                        <?php foreach ($series as $ser): ?>
                                            <option value="<?= $ser['id'] ?>" <?= ($ser['id'] == ($book['series_id'] ?? 0)) ? 'selected' : '' ?>>
                                                <?= e($ser['title']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sort_order_in_series" class="form-label">Порядок в серии</label>
                            <input type="number" class="form-control" id="sort_order_in_series" name="sort_order_in_series" 
                                value="<?= e($book['sort_order_in_series'] ?? '') ?>" 
                                placeholder="Номер по порядку в серии" min="1">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание книги</label>
                            <textarea class="form-control" id="description" name="description" 
                                      placeholder="Краткое описание сюжета или аннотация..." 
                                      rows="4"><?= e($book['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="published" name="published" value="1"
                                    <?= !empty($book['published']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="published">
                                    Опубликовать книгу (показывать на публичной странице автора)
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Сохранить изменения
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Главы книги</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($chapters)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Статус</th>
                                        <th>Слов</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($chapters as $chapter): ?>
                                    <tr>
                                        <td><?= e($chapter['title']) ?></td>
                                        <td>
                                            <span class="badge <?= $chapter['status'] == 'published' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= $chapter['status'] == 'published' ? 'Опубликована' : 'Черновик' ?>
                                            </span>
                                        </td>
                                        <td><?= $chapter['word_count'] ?></td>
                                        <td>
                                            <a href="<?= SITE_URL ?>/chapters/<?= $chapter['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i> Редактировать
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-text fs-1 text-muted"></i>
                            <p class="text-muted mt-2">В этой книге пока нет глав</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2">
                        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters" class="btn btn-outline-primary">
                            <i class="bi bi-list-ul"></i> Все главы
                        </a>
                        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters/create" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Добавить главу
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Обложка книги</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($book['cover_image'])): ?>
                        <div class="text-center mb-3">
                            <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                                 alt="Обложка" 
                                 class="img-fluid rounded" 
                                 style="max-height: 200px;">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="delete_cover" value="1" id="delete_cover">
                            <label class="form-check-label" for="delete_cover">
                                Удалить обложку
                            </label>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="cover_image" class="form-label">Загрузить новую обложку</label>
                        <input type="file" class="form-control" id="cover_image" name="cover_image" 
                               accept="image/jpeg, image/png, image/gif, image/webp">
                        <div class="form-text">
                            Разрешены: JPG, PNG, GIF, WebP. Максимальный размер: 5MB.
                        </div>
                    </div>
                    
                    <?php if (!empty($cover_error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= e($cover_error) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Публичная ссылка</h5>
                </div>
                <div class="card-body">
                    <div class="input-group mb-2">
                        <input type="text" 
                               id="share-link" 
                               value="<?= e(SITE_URL . '/book/' . $book['share_token']) ?>" 
                               readonly 
                               class="form-control">
                        <button type="button" onclick="copyShareLink()" class="btn btn-outline-secondary">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <form method="post" action="<?= SITE_URL ?>/books/<?= $book['id'] ?>/regenerate-token" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <button type="submit" class="btn btn-outline-warning btn-sm" onclick="return confirm('Создать новую ссылку? Старая ссылка перестанет работать.')">
                            <i class="bi bi-arrow-repeat"></i> Обновить ссылку
                        </button>
                    </form>
                    <div class="form-text">
                        В публичном просмотре отображаются только опубликованные главы
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Экспорт книги</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/pdf" class="btn btn-outline-danger" target="_blank">
                            <i class="bi bi-file-pdf"></i> PDF
                        </a>
                        <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/docx" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-file-word"></i> DOCX
                        </a>
                        <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/html" class="btn btn-outline-success" target="_blank">
                            <i class="bi bi-file-code"></i> HTML
                        </a>
                        <a href="<?= SITE_URL ?>/export/<?= $book['id'] ?>/txt" class="btn btn-outline-secondary" target="_blank">
                            <i class="bi bi-file-text"></i> TXT
                        </a>
                    </div>
                    <div class="form-text mt-2">
                        Экспортируются все главы книги (включая черновики)
                    </div>
                </div>
            </div>

            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">Опасная зона</h5>
                </div>
                <div class="card-body">
                    <p class="card-text small text-muted">
                        Удаление книги приведет к удалению всех глав и обложки. Это действие нельзя отменить.
                    </p>
                    <form method="post" action="<?= SITE_URL ?>/books/<?= $book['id'] ?>/delete" 
                          onsubmit="return confirm('Вы уверены, что хотите удалить книгу «<?= e($book['title']) ?>»? Все главы также будут удалены.');">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Удалить книгу
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyShareLink() {
    const shareLink = document.getElementById('share-link');
    shareLink.select();
    document.execCommand('copy');
    
    // Показать уведомление
    const button = event.target;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i>';
    button.classList.remove('btn-outline-secondary');
    button.classList.add('btn-success');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>

<?php include 'views/layouts/footer.php'; ?>