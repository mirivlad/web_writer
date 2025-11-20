<?php
require_once 'config/config.php';
require_once 'models/Book.php';
require_once 'views/header.php';

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
$books = $bookModel->findByUser($author_id, true); // только опубликованные (нужен параметр в модели)

$page_title = ($author['display_name'] ?: $author['username']) . ' — публичная страница';
include 'views/header.php';
?>

<h1><?= e($author['display_name'] ?: $author['username']) ?></h1>

<?php if (empty($books)): ?>
  <p>У этого автора пока нет опубликованных книг.</p>
<?php else: ?>
  <div class="grid">
    <?php foreach ($books as $b): ?>
      <article>
        <?php if ($b['cover_image']): ?>
          <img src="<?= e($b['cover_image']) ?>" alt="<?= e($b['title']) ?>" style="max-width:100%; height:auto;">
        <?php endif; ?>
        <h3><?= e($b['title']) ?></h3>
        <?php if ($b['description']): ?>
          <p><?= nl2br(e($b['description'])) ?></p>
        <?php endif; ?>
        <a href="view_book.php?share_token=<?= e($b['share_token']) ?>">Читать</a>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include 'views/footer.php'; ?>