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

$chapter_id = $_POST['chapter_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$chapter_id) {
    $_SESSION['error'] = "Не указана глава для удаления";
    redirect('books.php');
}

$chapterModel = new Chapter($pdo);

// Проверяем права доступа
if (!$chapterModel->userOwnsChapter($chapter_id, $user_id)) {
    $_SESSION['error'] = "У вас нет доступа к этой главе";
    redirect('books.php');
}


$chapter = $chapterModel->findById($chapter_id);
$book_id = $chapter['book_id'];

// Удаляем главу
if ($chapterModel->delete($chapter_id)) {
    $_SESSION['success'] = "Глава успешно удалена";
} else {
    $_SESSION['error'] = "Ошибка при удалении главы";
}

redirect("chapters.php?book_id=$book_id");
?>