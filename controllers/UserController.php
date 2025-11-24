<?php
// controllers/UserController.php
require_once 'controllers/BaseController.php';
require_once 'models/User.php';
require_once 'models/Book.php';
require_once 'includes/parsedown/ParsedownExtra.php';

class UserController extends BaseController {
    
    public function profile() {
        $this->requireLogin();
        
        $user_id = $_SESSION['user_id'];
        $userModel = new User($this->pdo);
        $user = $userModel->findById($user_id);

        $message = '';
        $avatar_error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $message = "Ошибка безопасности";
            } else {
                $display_name = trim($_POST['display_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $bio = trim($_POST['bio'] ?? '');
                
                // Обработка загрузки аватарки
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $avatar_result = handleAvatarUpload($_FILES['avatar'], $user_id);
                    if ($avatar_result['success']) {
                        $userModel->updateAvatar($user_id, $avatar_result['filename']);
                        // Обновляем данные пользователя
                        $user = $userModel->findById($user_id);
                    } else {
                        $avatar_error = $avatar_result['error'];
                    }
                }
                
                // Обработка удаления аватарки
                if (isset($_POST['delete_avatar']) && $_POST['delete_avatar'] == '1') {
                    deleteUserAvatar($user_id);
                    $user = $userModel->findById($user_id);
                }
                
                // Обновляем основные данные
                $data = [
                    'display_name' => $display_name,
                    'email' => $email,
                    'bio' => $bio
                ];
                
                if ($userModel->updateProfile($user_id, $data)) {
                    $_SESSION['display_name'] = $display_name ?: $user['username'];
                    $message = "Профиль обновлен";
                    // Обновляем данные пользователя
                    $user = $userModel->findById($user_id);
                } else {
                    $message = "Ошибка при обновлении профиля";
                }
            }
        }

        $this->render('user/profile', [
            'user' => $user,
            'message' => $message,
            'avatar_error' => $avatar_error,
            'page_title' => "Мой профиль"
        ]);
    }
    
    public function updateProfile() {
        $this->requireLogin();
        
        // Эта функция обрабатывает AJAX или прямые POST запросы для обновления профиля
        // Можно объединить с методом profile() или оставить отдельно для API-like операций
        $this->profile(); // Перенаправляем на основной метод
    }
    
    public function viewPublic($id) {
        $userModel = new User($this->pdo);
        $user = $userModel->findById($id);

        if (!$user) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $bookModel = new Book($this->pdo);
        $books = $bookModel->findByUser($id, true); // только опубликованные

        // Получаем статистику автора
        $total_books = count($books);
        $total_words = 0;
        $total_chapters = 0;

        foreach ($books as $book) {
            $book_stats = $bookModel->getBookStats($book['id'], true);
            $total_words += $book_stats['total_words'] ?? 0;
            $total_chapters += $book_stats['chapter_count'] ?? 0;
        }

        $Parsedown = new ParsedownExtra();

        $this->render('user/view_public', [
            'user' => $user,
            'books' => $books,
            'total_books' => $total_books,
            'total_words' => $total_words,
            'total_chapters' => $total_chapters,
            'Parsedown' => $Parsedown,
            'page_title' => ($user['display_name'] ?: $user['username']) . ' — публичная страница'
        ]);
    }
}
?>