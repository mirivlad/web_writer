<?php
include 'views/layouts/header.php';
?>

<div style="display: block; justify-content: between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <h1 style="margin: 0;">Мои серии книг</h1>
    <a href="/series/create" class="action-button primary" role="button">➕ Создать серию</a>
</div>

<?php if (empty($series)): ?>
    <article class="series-empty-state">
        <div class="series-empty-icon">📚</div>
        <h2>Пока нет серий</h2>
        <p style="color: #666; margin-bottom: 2rem;">
            Создайте свою первую серию, чтобы организовать книги в циклы и сериалы.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="/series/create" class="action-button primary" role="button">Создать серию</a>
            <a href="/books" class="action-button secondary">Перейти к книгам</a>
        </div>
    </article>
<?php else: ?>
    <div class="series-grid">
        <?php foreach ($series as $ser): ?>
            <article class="series-card">
                <div class="series-header">
                    <h3 class="series-title">
                        <a href="/series/<?= $ser['id'] ?>/edit"><?= e($ser['title']) ?></a>
                    </h3>
                    <div class="series-meta">
                        Создана <?= date('d.m.Y', strtotime($ser['created_at'])) ?>
                        <?php if ($ser['updated_at'] != $ser['created_at']): ?>
                            • Обновлена <?= date('d.m.Y', strtotime($ser['updated_at'])) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($ser['description'])): ?>
                    <div class="series-description">
                        <?= e($ser['description']) ?>
                    </div>
                <?php endif; ?>

                <div class="series-stats-grid">
                    <div class="series-stat">
                        <span class="series-stat-number"><?= $ser['book_count'] ?? 0 ?></span>
                        <span class="series-stat-label">книг</span>
                    </div>
                    <div class="series-stat">
                        <span class="series-stat-number"><?= number_format($ser['total_words'] ?? 0) ?></span>
                        <span class="series-stat-label">слов</span>
                    </div>
                    <div class="series-stat">
                        <span class="series-stat-number">
                            <?php
                            $avg_words = $ser['book_count'] > 0 ? round($ser['total_words'] / $ser['book_count']) : 0;
                            echo number_format($avg_words);
                            ?>
                        </span>
                        <span class="series-stat-label">слов/книга</span>
                    </div>
                </div>

                <div class="series-actions" style="display:grid;">
                    <a href="/series/<?= $ser['id'] ?>/edit" class="compact-button primary-btn" role="button">
                        ✏️ Управление
                    </a>
                    <a href="/series/<?= $ser['id'] ?>/view" class="compact-button secondary-btn" target="_blank" role="button">
                        👁️ Публично
                    </a>
                    <form method="post" action="/series/<?= $ser['id'] ?>/delete" 
                          onsubmit="return confirm('Удалить серию? Книги останутся, но будут удалены из серии.')">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <button type="submit" class="compact-button red-btn">🗑️ Удалить</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
include 'views/layouts/footer.php';
?>