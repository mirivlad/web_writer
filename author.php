<?php
require_once 'config/config.php';
require_once 'models/Book.php';

$author_id = (int)($_GET['id'] ?? 0);
if (!$author_id) {
    http_response_code(400);
    echo "<h2>Неверный запрос</h2>";
    include 'views/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, display_name FROM users WHERE id = ?");
$stmt->execute([$author_id]);
$author = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$author) {
    http_response_code(404);
    echo "<h2>Автор не найден</h2>";
    include 'views/footer.php';
    exit;
}

$bookModel = new Book($pdo);
$books = $bookModel->findByUser($author_id, true); // только опубликованные

$page_title = ($author['display_name'] ?: $author['username']) . ' — публичная страница';
include 'views/header.php';
?>

<h1><?= e($author['display_name'] ?: $author['username']) ?></h1>

<?php if (empty($books)): ?>
  <p>У этого автора пока нет опубликованных книг.</p>
<?php else: ?>
  <div class="grid">
    <?php foreach ($books as $b): ?>
      <article style="display: flex; gap: 1rem; align-items: flex-start;">
        <?php if ($b['cover_image']): ?>
          <div style="flex-shrink: 0;">
            <img src="<?= COVERS_URL . e($b['cover_image']) ?>" 
                 alt="<?= e($b['title']) ?>" 
                 style="max-width: 120px; height: auto; border-radius: 4px; border: 1px solid #ddd;"
                 onerror="this.style.display='none'">
          </div>
        <?php else: ?>
          <div style="flex-shrink: 0;">
            <div class="cover-placeholder" style="width: 120px; height: 160px;">📚</div>
          </div>
        <?php endif; ?>
        
        <div style="flex: 1;">
          <h3 style="margin-top: 0;"><?= e($b['title']) ?></h3>
          <?php if ($b['genre']): ?>
            <p style="color: #666; margin: 0.5rem 0;"><em><?= e($b['genre']) ?></em></p>
          <?php endif; ?>
          <?php if ($b['description']): ?>
            <p style="margin-bottom: 1rem;"><?= nl2br(e($b['description'])) ?></p>
          <?php endif; ?>
          <a href="view_book.php?share_token=<?= e($b['share_token']) ?>" class="adaptive-button">Читать</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include 'views/footer.php'; ?>