<?php
require_once 'config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Неверный метод запроса";
    redirect('books.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = "Ошибка безопасности";
    redirect('books.php');
}

$book_id = $_POST['book_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$book_id) {
    $_SESSION['error'] = "Не указана книга для удаления";
    redirect('books.php');
}

$bookModel = new Book($pdo);

// Проверяем права доступа
if (!$bookModel->userOwnsBook($book_id, $user_id)) {
    $_SESSION['error'] = "У вас нет доступа к этой книге";
    redirect('books.php');
}

// Получаем информацию о книге перед удалением
$book = $bookModel->findById($book_id);
if (!empty($book['cover_image'])) {
    $cover_path = COVERS_PATH . $book['cover_image'];
    if (file_exists($cover_path)) {
        unlink($cover_path);
    }
}
// Удаляем книгу
if ($bookModel->delete($book_id, $user_id)) {
    $_SESSION['success'] = "Книга «" . e($book['title']) . "» успешно удалена";
} else {
    $_SESSION['error'] = "Ошибка при удалении книги";
}

redirect('books.php');
?>