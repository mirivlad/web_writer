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
    $_SESSION['error'] = "Не указана книга";
    redirect('books.php');
}

$bookModel = new Book($pdo);

// Проверяем права доступа
if (!$bookModel->userOwnsBook($book_id, $user_id)) {
    $_SESSION['error'] = "У вас нет доступа к этой книге";
    redirect('books.php');
}

// Генерируем новый токен
$new_token = $bookModel->generateNewShareToken($book_id);

if ($new_token) {
    $_SESSION['success'] = "Публичная ссылка обновлена";
} else {
    $_SESSION['error'] = "Ошибка при обновлении ссылки";
}

redirect("book_edit.php?id=$book_id");
?>