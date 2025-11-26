<?php
// controllers/SeriesController.php
require_once 'controllers/BaseController.php';
require_once 'models/Series.php';
require_once 'models/Book.php';

class SeriesController extends BaseController {
    
    public function index() {
        $this->requireLogin();
        
        $user_id = $_SESSION['user_id'];
        $seriesModel = new Series($this->pdo);
        $series = $seriesModel->findByUser($user_id);

        // Получаем статистику для каждой серии отдельно
        foreach ($series as &$ser) {
            $stats = $seriesModel->getSeriesStats($ser['id'], $user_id);
            $ser['book_count'] = $stats['book_count'] ?? 0;
            $ser['total_words'] = $stats['total_words'] ?? 0;
        }
        unset($ser);

        $this->render('series/index', [
            'series' => $series,
            'page_title' => "Мои серии книг"
        ]);
    }
    
    public function create() {
        $this->requireLogin();
        
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $error = "Ошибка безопасности";
            } else {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');

                if (empty($title)) {
                    $error = "Название серии обязательно";
                } else {
                    $seriesModel = new Series($this->pdo);
                    $data = [
                        'title' => $title,
                        'description' => $description,
                        'user_id' => $_SESSION['user_id']
                    ];

                    if ($seriesModel->create($data)) {
                        $_SESSION['success'] = "Серия успешно создана";
                        $new_series_id = $this->pdo->lastInsertId();
                        $this->redirect("/series/{$new_series_id}/edit");
                    } else {
                        $error = "Ошибка при создании серии";
                    }
                }
            }
        }

        $this->render('series/create', [
            'error' => $error,
            'page_title' => "Создание новой серии"
        ]);
    }
    
    public function edit($id) {
        $this->requireLogin();
        
        $user_id = $_SESSION['user_id'];
        $seriesModel = new Series($this->pdo);
        $series = $seriesModel->findById($id);

        if (!$series || !$seriesModel->userOwnsSeries($id, $user_id)) {
            $_SESSION['error'] = "Серия не найдена или у вас нет доступа";
            $this->redirect('/series');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $error = "Ошибка безопасности";
            } else {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');

                if (empty($title)) {
                    $error = "Название серии обязательно";
                } else {
                    $data = [
                        'title' => $title,
                        'description' => $description,
                        'user_id' => $user_id
                    ];

                    if ($seriesModel->update($id, $data)) {
                        $_SESSION['success'] = "Серия успешно обновлена";
                        $this->redirect('/series');
                    } else {
                        $error = "Ошибка при обновлении серии";
                    }
                }
            }
        }

        // Получаем книги в серии
        $bookModel = new Book($this->pdo);
        $books_in_series = $bookModel->findBySeries($id);
        $available_books = $bookModel->getBooksNotInSeries($user_id, $id);

        $this->render('series/edit', [
            'series' => $series,
            'books_in_series' => $books_in_series,
            'available_books' => $available_books,
            'error' => $error,
            'page_title' => "Редактирование серии: " . e($series['title'])
        ]);
    }
    
    public function delete($id) {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Неверный метод запроса";
            $this->redirect('/series');
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Ошибка безопасности";
            $this->redirect('/series');
        }

        $user_id = $_SESSION['user_id'];
        $seriesModel = new Series($this->pdo);

        if (!$seriesModel->userOwnsSeries($id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой серии";
            $this->redirect('/series');
        }

        if ($seriesModel->delete($id, $user_id)) {
            $_SESSION['success'] = "Серия успешно удалена";
        } else {
            $_SESSION['error'] = "Ошибка при удалении серии";
        }

        $this->redirect('/series');
    }
    
    public function viewPublic($id) {
        $seriesModel = new Series($this->pdo);
        $series = $seriesModel->findById($id);

        if (!$series) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        // Получаем только опубликованные книги серии
        $books = $seriesModel->getBooksInSeries($id, true);

        // Получаем информацию об авторе
        $stmt = $this->pdo->prepare("SELECT id, username, display_name FROM users WHERE id = ?");
        $stmt->execute([$series['user_id']]);
        $author = $stmt->fetch(PDO::FETCH_ASSOC);

        // Получаем статистику по опубликованным книгам
        $bookModel = new Book($this->pdo);
        $total_words = 0;
        $total_chapters = 0;

        foreach ($books as $book) {
            $book_stats = $bookModel->getBookStats($book['id'], true);
            $total_words += $book_stats['total_words'] ?? 0;
            $total_chapters += $book_stats['chapter_count'] ?? 0;
        }

        $this->render('series/view_public', [
            'series' => $series,
            'books' => $books,
            'author' => $author,
            'total_words' => $total_words,
            'total_chapters' => $total_chapters,
            'page_title' => $series['title'] . ' — серия книг'
        ]);
    }

    public function addBook($series_id) {
        $this->requireLogin();
        
        $user_id = $_SESSION['user_id'];
        $seriesModel = new Series($this->pdo);
        $bookModel = new Book($this->pdo);

        if (!$seriesModel->userOwnsSeries($series_id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой серии";
            $this->redirect('/series');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Ошибка безопасности";
                $this->redirect("/series/{$series_id}/edit");
            }

            $book_id = (int)($_POST['book_id'] ?? 0);
            $sort_order = (int)($_POST['sort_order'] ?? 0);

            if (!$book_id) {
                $_SESSION['error'] = "Выберите книгу";
                $this->redirect("/series/{$series_id}/edit");
            }

            // Проверяем, что книга принадлежит пользователю
            if (!$bookModel->userOwnsBook($book_id, $user_id)) {
                $_SESSION['error'] = "У вас нет доступа к этой книге";
                $this->redirect("/series/{$series_id}/edit");
            }

            // Добавляем книгу в серию
            if ($bookModel->updateSeriesInfo($book_id, $series_id, $sort_order)) {
                $_SESSION['success'] = "Книга добавлена в серию";
            } else {
                $_SESSION['error'] = "Ошибка при добавлении книги в серию";
            }

            $this->redirect("/series/{$series_id}/edit");
        }
    }

    public function removeBook($series_id, $book_id) {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Неверный метод запроса";
            $this->redirect("/series/{$series_id}/edit");
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Ошибка безопасности";
            $this->redirect("/series/{$series_id}/edit");
        }

        $user_id = $_SESSION['user_id'];
        $seriesModel = new Series($this->pdo);
        $bookModel = new Book($this->pdo);

        if (!$seriesModel->userOwnsSeries($series_id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой серии";
            $this->redirect('/series');
        }

        // Проверяем, что книга принадлежит пользователю
        if (!$bookModel->userOwnsBook($book_id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой книге";
            $this->redirect("/series/{$series_id}/edit");
        }

        // Удаляем книгу из серии
        if ($bookModel->removeFromSeries($book_id)) {
            $_SESSION['success'] = "Книга удалена из серии";
        } else {
            $_SESSION['error'] = "Ошибка при удалении книги из серии";
        }

        $this->redirect("/series/{$series_id}/edit");
    }

    public function updateBookOrder($series_id) {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Неверный метод запроса";
            $this->redirect("/series/{$series_id}/edit");
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Ошибка безопасности";
            $this->redirect("/series/{$series_id}/edit");
        }

        $user_id = $_SESSION['user_id'];
        $seriesModel = new Series($this->pdo);
        $bookModel = new Book($this->pdo);

        if (!$seriesModel->userOwnsSeries($series_id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой серии";
            $this->redirect('/series');
        }

        $order_data = $_POST['order'] ?? [];
        
        if (empty($order_data)) {
            $_SESSION['error'] = "Нет данных для обновления";
            $this->redirect("/series/{$series_id}/edit");
        }

        // Обновляем порядок книг
        if ($bookModel->reorderSeriesBooks($series_id, $order_data)) {
            $_SESSION['success'] = "Порядок книг обновлен";
        } else {
            $_SESSION['error'] = "Ошибка при обновлении порядка книг";
        }

        $this->redirect("/series/{$series_id}/edit");
    }
}
?>