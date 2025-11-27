<?php
// controllers/ChapterController.php
require_once 'controllers/BaseController.php';
require_once 'models/Chapter.php';
require_once 'models/Book.php';

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
                $content = trim($_POST['content']) ?? '';
                $content = $this->cleanChapterContent($content);
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

        // Получаем главу и книгу
        $chapter = $chapterModel->findById($id);
        if (!$chapter) {
            if (!empty($_POST['autosave']) && $_POST['autosave'] === 'true') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Глава не найдена']);
                exit;
            }
            $_SESSION['error'] = "Глава не найдена";
            $this->redirect('/books');
        }

        $book = $bookModel->findById($chapter['book_id']);

        // Проверяем права доступа
        if (!$chapterModel->userOwnsChapter($id, $user_id)) {
            if (!empty($_POST['autosave']) && $_POST['autosave'] === 'true') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                exit;
            }
            $_SESSION['error'] = "У вас нет доступа к этой главе";
            $this->redirect('/books');
        }

        // Обработка POST запроса
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $content = $this->cleanChapterContent($_POST['content'] ?? '');
            $status = $_POST['status'] ?? 'draft';

            // Проверяем CSRF
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                if (!empty($_POST['autosave']) && $_POST['autosave'] === 'true') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Ошибка безопасности']);
                    exit;
                }
                $error = "Ошибка безопасности";
            }

            if (empty($title)) {
                $error = "Название главы обязательно";
            }

            $data = ['title' => $title, 'content' => $content, 'status' => $status];

            // Если это автосейв — возвращаем JSON сразу
            if (!empty($_POST['autosave']) && $_POST['autosave'] === 'true') {
                if (empty($error)) {
                    $success = $chapterModel->update($id, $data);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => $success, 'error' => $success ? null : 'Ошибка при сохранении']);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $error]);
                }
                exit;
            }

            // Обычное сохранение формы
            if (empty($error)) {
                if ($chapterModel->update($id, $data)) {
                    $_SESSION['success'] = "Глава успешно обновлена";
                    $this->redirect("/books/{$chapter['book_id']}/chapters");
                } else {
                    $error = "Ошибка при обновлении главы";
                }
            }
        }

        // Рендер страницы
        $this->render('chapters/edit', [
            'chapter' => $chapter,
            'book' => $book,
            'error' => $error ?? '',
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
        
        $content = $_POST['content'] ?? '';
        $content = $this->cleanChapterContent($content);
        $title = $_POST['title'] ?? 'Предпросмотр';
        
        $this->render('chapters/preview', [
            'content' => $content,
            'title' => $title,
            'page_title' => "Предпросмотр: " . e($title)
        ]);
    }

    public function updateOrder($book_id) {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'error' => 'Неверный метод запроса']);
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            return $this->jsonResponse(['success' => false, 'error' => 'Ошибка безопасности']);
        }

        $user_id = $_SESSION['user_id'];
        $chapterModel = new Chapter($this->pdo);
        $bookModel = new Book($this->pdo);

        // Проверяем права доступа к книге
        if (!$bookModel->userOwnsBook($book_id, $user_id)) {
            return $this->jsonResponse(['success' => false, 'error' => 'У вас нет доступа к этой книге']);
        }

        $order_data = $_POST['order'] ?? [];
        
        if (empty($order_data)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Нет данных для обновления']);
        }

        // Обновляем порядок глав
        if ($chapterModel->updateChaptersOrder($book_id, $order_data)) {
            return $this->jsonResponse(['success' => true]);
        } else {
            return $this->jsonResponse(['success' => false, 'error' => 'Ошибка при обновлении порядка глав']);
        }
    }

    function cleanChapterContent($content) {
        // Удаляем лишние пробелы в начале и конце
        $content = trim($content);
        
        // Удаляем пустые абзацы и параграфы, содержащие только пробелы
        $content = preg_replace('/<p[^>]*>\s*(?:<br\s*\/?>|&nbsp;)?\s*<\/p>/i', '', $content);
        $content = preg_replace('/<p[^>]*>\s*<\/p>/i', '', $content);
        
        // Удаляем последовательные пустые абзацы
        $content = preg_replace('/(<\/p>\s*<p[^>]*>)+/', '</p><p>', $content);
        
        // Удаляем лишние пробелы в начале и конце каждого параграфа
        $content = preg_replace('/(<p[^>]*>)\s+/', '$1', $content);
        $content = preg_replace('/\s+<\/p>/', '</p>', $content);
        
        // Удаляем лишние переносы строк
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        return $content;
    }

}
?>