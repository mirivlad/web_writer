<?php
// controllers/ChapterController.php
require_once 'controllers/BaseController.php';
require_once 'models/Chapter.php';
require_once 'models/Book.php';
require_once 'includes/parsedown/ParsedownExtra.php';

class ChapterController extends BaseController {
    
    public function index($book_id) {
        $this->requireLogin();
        
        $user_id = $_SESSION['user_id'];
        
        $bookModel = new Book($this->pdo);
        $chapterModel = new Chapter($this->pdo);

        // Проверяем права доступа к книге
        if (!$bookModel->userOwnsBook($book_id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой книге";
            $this->redirect('/books');
        }

        // Получаем информацию о книге и главах
        $book = $bookModel->findById($book_id);
        $chapters = $chapterModel->findByBook($book_id);

        $this->render('chapters/index', [
            'book' => $book,
            'chapters' => $chapters,
            'page_title' => "Главы книги: " . e($book['title'])
        ]);
    }
    
    public function create($book_id) {
        $this->requireLogin();
        
        $user_id = $_SESSION['user_id'];
        
        $bookModel = new Book($this->pdo);
        $chapterModel = new Chapter($this->pdo);

        // Проверяем права доступа к книге
        if (!$bookModel->userOwnsBook($book_id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой книге";
            $this->redirect('/books');
        }

        $book = $bookModel->findById($book_id);
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $error = "Ошибка безопасности";
            } else {
                $title = trim($_POST['title'] ?? '');
                $content = $_POST['content'] ?? '';
                $status = $_POST['status'] ?? 'draft';

                if (empty($title)) {
                    $error = "Название главы обязательно";
                } else {
                    $data = [
                        'book_id' => $book_id,
                        'title' => $title,
                        'content' => $content,
                        'status' => $status
                    ];

                    if ($chapterModel->create($data)) {
                        $_SESSION['success'] = "Глава успешно создана";
                        $this->redirect("/books/{$book_id}/chapters");
                    } else {
                        $error = "Ошибка при создании главы";
                    }
                }
            }
        }

        $this->render('chapters/create', [
            'book' => $book,
            'error' => $error,
            'page_title' => "Новая глава для: " . e($book['title'])
        ]);
    }
    
    public function edit($id) {
        $this->requireLogin();
        
        $user_id = $_SESSION['user_id'];
        
        $chapterModel = new Chapter($this->pdo);
        $bookModel = new Book($this->pdo);

        // Проверяем права доступа к главе
        if (!$chapterModel->userOwnsChapter($id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой главе";
            $this->redirect('/books');
        }

        $chapter = $chapterModel->findById($id);
        $book = $bookModel->findById($chapter['book_id']);
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $error = "Ошибка безопасности";
            } else {
                $title = trim($_POST['title'] ?? '');
                $content = $_POST['content'] ?? '';
                $status = $_POST['status'] ?? 'draft';

                if (empty($title)) {
                    $error = "Название главы обязательно";
                } else {
                    $data = [
                        'title' => $title,
                        'content' => $content,
                        'status' => $status
                    ];

                    if ($chapterModel->update($id, $data)) {
                        $_SESSION['success'] = "Глава успешно обновлена";
                        $this->redirect("/books/{$chapter['book_id']}/chapters");
                    } else {
                        $error = "Ошибка при обновлении главы";
                    }
                }
            }
        }

        $this->render('chapters/edit', [
            'chapter' => $chapter,
            'book' => $book,
            'error' => $error,
            'page_title' => "Редактирование главы: " . e($chapter['title'])
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
        $chapterModel = new Chapter($this->pdo);

        // Проверяем права доступа
        if (!$chapterModel->userOwnsChapter($id, $user_id)) {
            $_SESSION['error'] = "У вас нет доступа к этой главе";
            $this->redirect('/books');
        }

        $chapter = $chapterModel->findById($id);
        $book_id = $chapter['book_id'];

        // Удаляем главу
        if ($chapterModel->delete($id)) {
            $_SESSION['success'] = "Глава успешно удалена";
        } else {
            $_SESSION['error'] = "Ошибка при удалении главы";
        }

        $this->redirect("/books/{$book_id}/chapters");
    }
    
    public function preview() {
        $this->requireLogin();
        
        require_once 'includes/parsedown/ParsedownExtra.php';
        $Parsedown = new ParsedownExtra();

        $content = $_POST['content'] ?? '';
        $title = $_POST['title'] ?? 'Предпросмотр';
        $editor_type = $_POST['editor_type'] ?? 'markdown';

        // Обрабатываем контент в зависимости от типа редактора
        if ($editor_type == 'markdown') {
            $html_content = $Parsedown->text($content);
        } else {
            $html_content = $content;
        }

        $this->render('chapters/preview', [
            'content' => $html_content,
            'title' => $title,
            'page_title' => "Предпросмотр: " . e($title)
        ]);
    }
}
?>