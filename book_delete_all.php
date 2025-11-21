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

$user_id = $_SESSION['user_id'];
$bookModel = new Book($pdo);

// Получаем все книги пользователя
$books = $bookModel->findByUser($user_id);

if (empty($books)) {
    $_SESSION['error'] = "У вас нет книг для удаления";
    redirect('books.php');
}

$deleted_count = 0;
$error_count = 0;

// Удаляем каждую книгу
foreach ($books as $book) {
    if ($bookModel->delete($book['id'], $user_id)) {
        $deleted_count++;
    } else {
        $error_count++;
    }
}

if ($error_count === 0) {
    $_SESSION['success'] = "Все книги успешно удалены ($deleted_count книг)";
} else {
    $_SESSION['error'] = "Удалено $deleted_count книг, не удалось удалить $error_count книг";
}

redirect('books.php');
?>