<?php
require_once 'config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Неверный метод запроса";
    redirect('series.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = "Ошибка безопасности";
    redirect('series.php');
}

$series_id = $_POST['series_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$series_id) {
    $_SESSION['error'] = "Не указана серия для удаления";
    redirect('series.php');
}

$seriesModel = new Series($pdo);

if (!$seriesModel->userOwnsSeries($series_id, $user_id)) {
    $_SESSION['error'] = "У вас нет доступа к этой серии";
    redirect('series.php');
}

$series = $seriesModel->findById($series_id);

if ($seriesModel->delete($series_id, $user_id)) {
    $_SESSION['success'] = "Серия «" . e($series['title']) . "» успешно удалена";
} else {
    $_SESSION['error'] = "Ошибка при удалении серии";
}

redirect('series.php');
?>