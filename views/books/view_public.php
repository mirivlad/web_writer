<?php
// views/books/view_public.php
include 'views/layouts/header.php';
?>

<div class="container">
    <article style="max-width: 800px; margin: 0 auto;">
        <header style="text-align: center; margin-bottom: 2rem;">
            <?php if (!empty($book['cover_image'])): ?>
                <div style="margin-bottom: 1rem;">
                    <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                         alt="<?= e($book['title']) ?>" 
                         style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"
                         onerror="this.style.display='none'">
                </div>
            <?php endif; ?>
            
            <h1 style="margin-bottom: 0.5rem;"><?= e($book['title']) ?></h1>
            
            <p style="color: #666; font-style: italic; margin-bottom: 0.5rem;">
                Автор: <a href="<?= SITE_URL ?>/author/<?= $book['user_id'] ?>"><?= e($author_name) ?></a>
            </p>
            
            <?php if (!empty($book['genre'])): ?>
                <p style="color: #666; font-style: italic; margin-bottom: 1rem;">
                    <?= e($book['genre']) ?>
                </p>
            <?php endif; ?>
            
            <?php if (!empty($book['description'])): ?>
                <div style="background: var(--card-background-color); padding: 1.5rem; border-radius: 8px; margin: 1rem 0; text-align: left;">
                    <?= nl2br(e($book['description'])) ?>
                </div>
            <?php endif; ?>
            
            <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; font-size: 0.9em; color: #666;">
                <span>Глав: <?= count($chapters) ?></span>
                <span>Слов: <?= array_sum(array_column($chapters, 'word_count')) ?></span>
                <a href="<?= SITE_URL ?>/export/shared/<?= $book['share_token'] ?>" class="adaptive-button secondary" style="font-size: 0.8em;">
                    📄 Скачать книгу
                </a>
            </div>
        </header>

        <?php if (empty($chapters)): ?>
            <div style="text-align: center; padding: 3rem; background: var(--card-background-color); border-radius: 5px;">
                <h3>В этой книге пока нет глав</h3>
                <p>Автор еще не опубликовал содержание книги</p>
            </div>
        <?php else: ?>
            <h2 style="text-align: center; margin-bottom: 2rem;">Оглавление</h2>
            
            <div class="chapters-list">
                <?php foreach ($chapters as $index => $chapter): ?>
                    <article style="margin-bottom: 1rem; padding: 1rem; background: var(--card-background-color); border-radius: 8px;">
                        <h3 style="margin-top: 0;">
                            <a href="#chapter-<?= $chapter['id'] ?>" style="text-decoration: none;">
                                Глава <?= $index + 1 ?>: <?= e($chapter['title']) ?>
                            </a>
                        </h3>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <small style="color: var(--muted-color);">
                                Слов: <?= $chapter['word_count'] ?> 
                                | Обновлено: <?= date('d.m.Y', strtotime($chapter['updated_at'])) ?>
                            </small>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <hr style="margin: 2rem 0;">

            <?php foreach ($chapters as $index => $chapter): ?>
                <div class="chapter-content" id="chapter-<?= $chapter['id'] ?>" style="margin-bottom: 3rem;">
                    <h2 style="border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">
                        Глава <?= $index + 1 ?>: <?= e($chapter['title']) ?>
                    </h2>
                    
                    <div style="margin-top: 1.5rem; line-height: 1.6;">
                        <?php if ($book['editor_type'] == 'markdown'): ?>
                            <?= $Parsedown->text($chapter['content']) ?>
                        <?php else: ?>
                            <?= $chapter['content'] ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($index < count($chapters) - 1): ?>
                        <div style="text-align: center; margin-top: 2rem;">
                            <a href="#chapter-<?= $chapters[$index + 1]['id'] ?>" class="button">
                                📖 Следующая глава
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <footer style="margin-top: 3rem; padding-top: 1rem; border-top: 2px solid var(--muted-border-color); text-align: center;">
            <p style="color: var(--muted-color);">
                Книга создана в <?= e(APP_NAME) ?> • 
                <?= date('Y') ?>
            </p>
        </footer>
    </article>
</div>

<style>
.chapter-content h1, .chapter-content h2, .chapter-content h3 {
    margin-top: 1.5em;
    margin-bottom: 0.5em;
}

.chapter-content p {
    margin-bottom: 1em;
    text-align: justify;
}

.chapter-content .dialogue {
    margin-left: 2rem;
    font-style: italic;
    color: #2c5aa0;
}

.chapter-content blockquote {
    border-left: 4px solid var(--primary);
    padding-left: 1rem;
    margin-left: 0;
    color: #555;
    font-style: italic;
}

.chapter-content code {
    background: var(--card-background-color);
    padding: 2px 4px;
    border-radius: 3px;
}

.chapter-content pre {
    background: var(--card-background-color);
    padding: 1rem;
    border-radius: 5px;
    overflow-x: auto;
}

.chapter-content ul, .chapter-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}
</style>

<?php include 'views/layouts/footer.php'; ?>