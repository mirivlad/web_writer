<?php
// views/user/view_public.php
include 'views/layouts/header.php';
?>

<div class="container" style="width:100%; margin-left: 0em; margin-right: 0em auto;">
    <article>
        <header style="text-align: center; margin-bottom: 2rem; border-bottom: 2px solid var(--muted-border-color); padding-bottom: 1rem;">
            <!-- Аватарка автора -->
            <div style="margin-bottom: 1rem;">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= AVATARS_URL . e($user['avatar']) ?>" 
                         alt="<?= e($user['display_name'] ?: $user['username']) ?>" 
                         style="width: 150px; height: 150px; border-radius: 50%; border: 3px solid var(--primary); object-fit: cover;"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <div style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; margin: 0 auto;">
                        <?= mb_substr(e($user['display_name'] ?: $user['username']), 0, 1) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <h1 style="margin-bottom: 0.5rem;"><?= e($user['display_name'] ?: $user['username']) ?></h1>
            
            <!-- Биография автора -->
            <?php if (!empty($user['bio'])): ?>
                <div style="background: var(--card-background-color); padding: 1.5rem; border-radius: 8px; margin: 1rem 0; text-align: left;">
                    <?= e($user['bio']) ?>
                </div>
            <?php endif; ?>
            
            <!-- Статистика автора -->
            <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; font-size: 0.9em; color: var(--muted-color);">
                <div style="text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: var(--primary);"><?= $total_books ?></div>
                    <div>Книг</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: var(--success);"><?= $total_chapters ?></div>
                    <div>Глав</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: var(--warning);"><?= $total_words ?></div>
                    <div>Слов</div>
                </div>
            </div>
        </header>

        <h2 style="text-align: center; margin-bottom: 2rem;">Публикации автора</h2>

        <?php if (empty($books)): ?>
            <div style="text-align: center; padding: 3rem; background: var(--card-background-color); border-radius: 5px;">
                <h3>У этого автора пока нет опубликованных книг</h3>
                <p>Следите за обновлениями, скоро здесь появятся новые произведения!</p>
            </div>
        <?php else: ?>
            <div class="author-books">
                <?php foreach ($books as $book): ?>
                    <article style="display: flex; gap: 1rem; align-items: flex-start; margin-bottom: 2rem; padding: 1.5rem; background: var(--card-background-color); border-radius: 8px;">
                        <?php if ($book['cover_image']): ?>
                            <div style="flex-shrink: 0;">
                                <img src="<?= COVERS_URL . e($book['cover_image']) ?>" 
                                     alt="<?= e($book['title']) ?>" 
                                     style="max-width: 120px; height: auto; border-radius: 4px; border: 1px solid var(--border-color);"
                                     onerror="this.style.display='none'">
                            </div>
                        <?php else: ?>
                            <div style="flex-shrink: 0;">
                                <div class="cover-placeholder" style="width: 120px; height: 160px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                                    📚
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div style="flex: 1;">
                            <h3 style="margin-top: 0;"><?= e($book['title']) ?></h3>
                            
                            <?php if ($book['genre']): ?>
                                <p style="color: var(--muted-color); margin: 0.5rem 0;"><em><?= e($book['genre']) ?></em></p>
                            <?php endif; ?>
                            
                            <?php if ($book['description']): ?>
                                <p style="margin-bottom: 1rem;"><?= nl2br(e($book['description'])) ?></p>
                            <?php endif; ?>
                            
                            <?php
                            $book_stats = $bookModel->getBookStats($book['id'], true);
                            $chapter_count = $book_stats['chapter_count'] ?? 0;
                            $word_count = $book_stats['total_words'] ?? 0;
                            ?>
                            
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                <a href="<?= SITE_URL ?>/book/<?= e($book['share_token']) ?>" class="adaptive-button">
                                    Читать книгу
                                </a>
                                
                                <small style="color: var(--muted-color);">
                                    Глав: <?= $chapter_count ?> | Слов: <?= $word_count ?>
                                </small>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <footer style="margin-top: 3rem; padding-top: 1rem; border-top: 2px solid var(--muted-border-color); text-align: center;">
            <p style="color: var(--muted-color);">
                Страница автора создана в <?= e(APP_NAME) ?> • 
                <?= date('Y') ?>
            </p>
        </footer>
    </article>
</div>

<style>
.author-books article {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid var(--border-color);
}

.author-books article:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.cover-placeholder {
    width: 120px;
    height: 160px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
}

@media (max-width: 768px) {
    .author-books article {
        flex-direction: column;
        text-align: center;
    }
    
    .author-books .book-cover {
        align-self: center;
    }
    
    header .author-stats {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

<?php include 'views/layouts/footer.php'; ?>