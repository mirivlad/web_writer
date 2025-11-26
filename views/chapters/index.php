<?php
// views/chapters/index.php
include 'views/layouts/header.php';
?>

<div style="margin-bottom: 1rem;">
    <h1 style="margin: 0 0 0.5rem 0; font-size: 1.5rem;">Главы книги: <?= e($book['title']) ?></h1>
    <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content:center;">
        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters/create" class="adaptive-button" role="button">➕ Новая глава</a>
        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/edit" class="adaptive-button secondary" role="button">✏️ Редактировать книгу</a>
        <a href="<?= SITE_URL ?>/book/<?= $book['share_token'] ?>" class="adaptive-button green-btn" role="button" target="_blank">👁️ Публичный доступ</a>
        <a href="<?= SITE_URL ?>/book/all/<?= $book['id'] ?>" class="adaptive-button" role="button" target="_blank">👁️ Полный обзор</a>
    </div>
</div>

<?php if (empty($chapters)): ?>
    <div style="text-align: center; padding: 2rem; background: var(--card-background-color); border-radius: 5px; margin-top: 1rem;">
        <h3>В этой книге пока нет глав</h3>
        <p>Создайте первую главу для вашей книги</p>
        <a href="<?= SITE_URL ?>/books/<?= $book['id'] ?>/chapters/create" class="adaptive-button">📝 Создать первую главу</a>
    </div>
<?php else: ?>
    <div style="overflow-x: auto; margin-top: 1rem;">
        <table class="compact-table">
            <thead>
                <tr>
                    <th style="width: 5%;">№</th>
                    <th style="width: 40%;">Название главы</th>
                    <th style="width: 15%;">Статус</th>
                    <th style="width: 10%;">Слов</th>
                    <th style="width: 20%;">Обновлено</th>
                    <th style="width: 10%;">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chapters as $index => $chapter): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                        <strong><?= e($chapter['title']) ?></strong>
                        <?php if ($chapter['content']): ?>
                            <br><small style="color: var(--muted-color);"><?= e(mb_strimwidth($chapter['content'], 0, 100, '...')) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="color: <?= $chapter['status'] == 'published' ? 'green' : 'orange' ?>">
                            <?= $chapter['status'] == 'published' ? '✅ Опубликована' : '📝 Черновик' ?>
                        </span>
                    </td>
                    <td><?= $chapter['word_count'] ?></td>
                    <td>
                        <small><?= date('d.m.Y H:i', strtotime($chapter['updated_at'])) ?></small>
                    </td>
                    <td>
                        <div style="display: flex; gap: 3px; flex-wrap: wrap;">
                            <a href="<?= SITE_URL ?>/chapters/<?= $chapter['id'] ?>/edit" class="compact-button secondary" title="Редактировать" role="button">
                                ✏️
                            </a>
                            <form method="post" action="<?= SITE_URL ?>/chapters/<?= $chapter['id'] ?>/delete" style="display: inline;" onsubmit="return confirm('Вы уверены, что хотите удалить эту главу? Это действие нельзя отменить.');">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <button type="submit" class="compact-button secondary" style="background: #ff4444; border-color: #ff4444; color: white;" title="Удалить">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1rem; padding: 0.5rem; background: var(--card-background-color); border-radius: 3px;">
        <strong>Статистика:</strong> 
        Всего глав: <?= count($chapters) ?> | 
        Всего слов: <?= array_sum(array_column($chapters, 'word_count')) ?> |
        Опубликовано: <?= count(array_filter($chapters, function($ch) { return $ch['status'] == 'published'; })) ?>
    </div>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>