<?php
// controllers/BookController.php
require_once 'controllers/BaseController.php';
require_once 'models/Book.php';
require_once 'models/Chapter.php';
require_once 'models/Series.php';

class BookController extends BaseController {
    public function index() {
        $this->requireLogin();
        $user_id = $_SESSION['user_id'];
        $bookModel = new Book($this->pdo);
        $books = $bookModel->findByUser($user_id);
        $this->render('books/index', [
            'books' => $books,
            'page_title' => 'Мои книги'
        ]);
    }
    
    public function create() {
        $this->requireLogin();
        $seriesModel = new Series($this->pdo);
        $series = $seriesModel->findByUser($_SESSION['user_id']);
        
        // Возвращаем типы редакторов для выбора
        $editor_types = [
            'markdown' => 'Markdown редактор',
            'html' => 'HTML редактор (TinyMCE)'
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Ошибка безопасности";
                $this->redirect('/books/create');
            }
            
            $title = trim($_POST['title'] ?? '');
            if (empty($title)) {
                $_SESSION['error'] = "Название книги обязательно";
                $this->redirect('/books/create');
            }
            
            $bookModel = new Book($this->pdo);
            $data = [
                'title' => $title,
                'description' => trim($_POST['description'] ?? ''),
                'genre' => trim($_POST['genre'] ?? ''),
                'user_id' => $_SESSION['user_id'],
                'editor_type' => $_POST['editor_type'] ?? 'markdown',
                'series_id' => !empty($_POST['series_id']) ? (int)$_POST['series_id'] : null,
                'sort_order_in_series' => !empty($_POST['sort_order_in_series']) ? (int)$_POST['sort_order_in_series'] : null,
                'published' => isset($_POST['published']) ? 1 : 0
            ];
            
            if ($bookModel->create($data)) {
                $_SESSION['success'] = "Книга успешно создана";
                $new_book_id = $this->pdo->lastInsertId();
                $this->redirect("/books/{$new_book_id}/edit");
            } else {
                $_SESSION['error'] = "Ошибка при создании книги";
            }
        }
        
        $this->render('books/create', [
            'series' => $series,
            'editor_types' => $editor_types,
            'selected_editor' => 'markdown', // по умолчанию
            'page_title' => 'Создание новой книги'
        ]);
    }
    
    public function edit($id) {
        $this->requireLogin();
        $bookModel = new Book($this->pdo);
        $book = $bookModel->findById($id);
        
        if (!$book || $book['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Книга не найдена или у вас нет доступа";
            $this->redirect('/books');
        }
        
        $seriesModel = new Series($this->pdo);
        $series = $seriesModel->findByUser($_SESSION['user_id']);
        
        // Типы редакторов для выбора
        $editor_types = [
            'markdown' => 'Markdown редактор',
            'html' => 'HTML редактор (TinyMCE)'
        ];
        
        $error = '';
        $cover_error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $error = "Ошибка безопасности";
            } else {
                $title = trim($_POST['title'] ?? '');
                if (empty($title)) {
                    $error = "Название книги обязательно";
                } else {
                    $old_editor_type = $book['editor_type'];
                    $new_editor_type = $_POST['editor_type'] ?? 'markdown';
                    $editor_changed = ($old_editor_type !== $new_editor_type);
                    
                    $data = [
                        'title' => $title,
                        'description' => trim($_POST['description'] ?? ''),
                        'genre' => trim($_POST['genre'] ?? ''),
                        'user_id' => $_SESSION['user_id'],
                        'editor_type' => $new_editor_type,
                        'series_id' => !empty($_POST['series_id']) ? (int)$_POST['series_id'] : null,
                        'sort_order_in_series' => !empty($_POST['sort_order_in_series']) ? (int)$_POST['sort_order_in_series'] : null,
                        'published' => isset($_POST['published']) ? 1 : 0
                    ];
                    
                    // Обработка смены редактора (прежде чем обновлять книгу)
                    if ($editor_changed) {
                        $conversion_success = $bookModel->convertChaptersContent($id, $old_editor_type, $new_editor_type);
                        if (!$conversion_success) {
                            $_SESSION['warning'] = "Внимание: не удалось автоматически сконвертировать содержание всех глав.";
                        }
                    }
                    
                    // Обработка обложки
                    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                        $cover_result = handleCoverUpload($_FILES['cover_image'], $id);
                        if ($cover_result['success']) {
                            $bookModel->updateCover($id, $cover_result['filename']);
                        } else {
                            $cover_error = $cover_result['error'];
                        }
                    }
                    
                    // Удаление обложки
                    if (isset($_POST['delete_cover']) && $_POST['delete_cover'] == '1') {
                        $bookModel->deleteCover($id);
                    }
                    
                    // Обновление книги
                    $success = $bookModel->update($id, $data);
                    
                    if ($success) {
                        $success_message = "Книга успешно обновлена";
                        if ($editor_changed) {
                            $success_message .= ". Содержание глав сконвертировано в новый формат.";
                        }
                        $_SESSION['success'] = $success_message;
                        $this->redirect("/books/{$id}/edit");
                    } else {
                        $error = "Ошибка при обновлении книги";
                    }
                }
            }
        }
        
        // Получаем статистику по главам для отображения в шаблоне
        $chapterModel = new Chapter($this->pdo);
        $chapters = $chapterModel->findByBook($id);
        
        $this->render('books/edit', [
            'book' => $book,
            'series' => $series,
            'chapters' => $chapters,
            'editor_types' => $editor_types,
            'error' => $error,
            'cover_error' => $cover_error,
            'page_title' => 'Редактирование книги'
        ]);
    }
    
    public function delete($id) {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Неверный метод запроса";
            $this->redirect('/books');
        }
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Ошибка безопасности";
            $this->redirect('/books');
        }
        $user_id = $_SESSION['user_id'];
        $bookModel = new Book($this->pdo);
        if (!$bookModel->userOwnsBook($id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой книге";
            $this->redirect('/books');
        }
        if ($bookModel->delete($id, $user_id)) {
            $_SESSION['success'] = "Книга успешно удалена";
        } else {
            $_SESSION['error'] = "Ошибка при удалении книги";
        }
        $this->redirect('/books');
    }
    
    public function viewPublic($share_token) {
        $bookModel = new Book($this->pdo);
        $book = $bookModel->findByShareToken($share_token);
        if (!$book) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        $chapters = $bookModel->getPublishedChapters($book['id']);
        
        // Получаем информацию об авторе
        $stmt = $this->pdo->prepare("SELECT id, username, display_name FROM users WHERE id = ?");
        $stmt->execute([$book['user_id']]);
        $author = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->render('books/view_public', [
            'book' => $book,
            'chapters' => $chapters,
            'author' => $author,
            'page_title' => $book['title']
        ]);
    }
    
    public function normalizeContent($id) {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Неверный метод запроса";
            $this->redirect("/books/{$id}/edit");
        }
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Ошибка безопасности";
            $this->redirect("/books/{$id}/edit");
        }
        $user_id = $_SESSION['user_id'];
        $bookModel = new Book($this->pdo);
        if (!$bookModel->userOwnsBook($id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой книге";
            $this->redirect('/books');
        }
        if ($bookModel->normalizeBookContent($id)) {
            $_SESSION['success'] = "Контент глав успешно нормализован";
        } else {
            $_SESSION['error'] = "Ошибка при нормализации контента";
        }
        $this->redirect("/books/{$id}/edit");
    }
    
    public function regenerateToken($id) {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Неверный метод запроса";
            $this->redirect("/books/{$id}/edit");
        }
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Ошибка безопасности";
            $this->redirect("/books/{$id}/edit");
        }
        $user_id = $_SESSION['user_id'];
        $bookModel = new Book($this->pdo);
        if (!$bookModel->userOwnsBook($id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой книге";
            $this->redirect('/books');
        }
        $new_token = $bookModel->generateNewShareToken($id);
        if ($new_token) {
            $_SESSION['success'] = "Ссылка успешно обновлена";
        } else {
            $_SESSION['error'] = "Ошибка при обновлении ссылки";
        }
        $this->redirect("/books/{$id}/edit");
    }
}
?>