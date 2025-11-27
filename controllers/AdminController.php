<?php
require_once 'controllers/BaseController.php';
require_once 'models/User.php';

class AdminController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
    }
    
    
    public function users() {
        $userModel = new User($this->pdo);
        
        // Параметры пагинации
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        
        // Валидация параметров
        $current_page = max(1, $current_page);
        $allowed_per_page = [5, 10, 50, 100];
        if (!in_array($per_page, $allowed_per_page)) {
            $per_page = 10;
        }
        
        // Получаем общее количество пользователей
        $total_users = $userModel->getTotalUsersCount();
        
        // Вычисляем общее количество страниц
        $total_pages = ceil($total_users / $per_page);
        
        // Корректируем текущую страницу, если она выходит за пределы
        if ($current_page > $total_pages && $total_pages > 0) {
            $current_page = $total_pages;
        }
        
        // Вычисляем смещение
        $offset = ($current_page - 1) * $per_page;
        
        // Получаем пользователей для текущей страницы
        $users = $userModel->getUsersPaginated($offset, $per_page);
        
        // Генерируем пагинацию
        $pagination = $this->generatePagination($current_page, $total_pages);
        
        $this->render('admin/users', [
            'users' => $users,
            'page_title' => 'Управление пользователями',
            'pagination' => $pagination,
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'per_page' => $per_page,
            'total_users' => $total_users,
            'allowed_per_page' => $allowed_per_page
        ]);
    }

    private function generatePagination($current_page, $total_pages) {
        if ($total_pages <= 1) {
            return [];
        }
        
        $pagination = [];
        $max_visible_pages = 5; // Количество видимых страниц до и после текущей
        
        // Всегда добавляем первую страницу
        $pagination[] = [
            'page' => 1,
            'label' => '1',
            'active' => (1 == $current_page),
            'type' => 'page'
        ];
        
        // Определяем диапазон видимых страниц
        $start_page = max(2, $current_page - $max_visible_pages);
        $end_page = min($total_pages - 1, $current_page + $max_visible_pages);
        
        // Добавляем многоточие после первой страницы, если нужно
        if ($start_page > 2) {
            $pagination[] = [
                'page' => null,
                'label' => '...',
                'active' => false,
                'type' => 'ellipsis'
            ];
        }
        
        // Добавляем видимые страницы
        for ($i = $start_page; $i <= $end_page; $i++) {
            $pagination[] = [
                'page' => $i,
                'label' => $i,
                'active' => ($i == $current_page),
                'type' => 'page'
            ];
        }
        
        // Добавляем многоточие перед последней страницей, если нужно
        if ($end_page < $total_pages - 1) {
            $pagination[] = [
                'page' => null,
                'label' => '...',
                'active' => false,
                'type' => 'ellipsis'
            ];
        }
        
        // Добавляем последнюю страницу, если она не первая
        if ($total_pages > 1) {
            $pagination[] = [
                'page' => $total_pages,
                'label' => $total_pages,
                'active' => ($total_pages == $current_page),
                'type' => 'page'
            ];
        }
        
        return $pagination;
    }
    
    public function toggleUserStatus($user_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Неверный метод запроса или токен безопасности";
            $this->redirect('/admin/users');
            return;
        }
        // Сохраняем параметры пагинации
        $page = $_GET['page'] ?? 1;
        $per_page = $_GET['per_page'] ?? 10;
        
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "Нельзя изменить статус собственного аккаунта";
            $this->redirect("/admin/users?page=$page&per_page=$per_page");
            return;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($user_id);
        
        if (!$user) {
            $_SESSION['error'] = "Пользователь не найден";
            $this->redirect("/admin/users?page=$page&per_page=$per_page");
            return;
        }
        
        $newStatus = $user['is_active'] ? 0 : 1;
        if ($userModel->updateStatus($user_id, $newStatus)) {
            $_SESSION['success'] = "Статус пользователя обновлен";
        } else {
            $_SESSION['error'] = "Ошибка при обновлении статуса";
        }
        
        $this->redirect("/admin/users?page=$page&per_page=$per_page");
    }
    
    public function deleteUser($user_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Неверный метод запроса или токен безопасности";
            $this->redirect('/admin/users');
            return;
        }
        
        // Сохраняем параметры пагинации
        $page = $_GET['page'] ?? 1;
        $per_page = $_GET['per_page'] ?? 10;
        
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "Нельзя удалить собственный аккаунт";
            $this->redirect("/admin/users?page=$page&per_page=$per_page");
            return;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($user_id);
        
        if (!$user) {
            $_SESSION['error'] = "Пользователь не найден";
            $this->redirect("/admin/users?page=$page&per_page=$per_page");
            return;
        }
        
        if ($userModel->delete($user_id)) {
            $_SESSION['success'] = "Пользователь успешно удален";
        } else {
            $_SESSION['error'] = "Ошибка при удалении пользователя";
        }
        
        $this->redirect("/admin/users?page=$page&per_page=$per_page");
    }
    
    public function addUser() {
        $error = '';
        $success = '';
        
        // Сохраняем параметры пагинации для возврата
        $return_page = $_GET['page'] ?? 1;
        $return_per_page = $_GET['per_page'] ?? 10;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $error = "Ошибка безопасности";
            } else {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $password_confirm = $_POST['password_confirm'] ?? '';
                $email = trim($_POST['email'] ?? '');
                $display_name = trim($_POST['display_name'] ?? '');
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if (empty($username) || empty($password)) {
                    $error = 'Имя пользователя и пароль обязательны';
                } elseif ($password !== $password_confirm) {
                    $error = 'Пароли не совпадают';
                } elseif (strlen($password) < 6) {
                    $error = 'Пароль должен быть не менее 6 символов';
                } else {
                    $userModel = new User($this->pdo);
                    if ($userModel->findByUsername($username)) {
                        $error = 'Имя пользователя уже занято';
                    } elseif (!empty($email) && $userModel->findByEmail($email)) {
                        $error = 'Email уже используется';
                    } else {
                        $data = [
                            'username' => $username,
                            'password' => $password,
                            'email' => $email ?: null,
                            'display_name' => $display_name ?: $username,
                            'is_active' => $is_active
                        ];
                        
                        if ($userModel->create($data)) {
                            $_SESSION['success'] = 'Пользователь успешно создан';
                            $this->redirect("/admin/users?page=1&per_page=$return_per_page");
                            return;
                        } else {
                            $error = 'Ошибка при создании пользователя';
                        }
                    }
                }
            }
        }
        
        $this->render('admin/add_user', [
            'error' => $error,
            'success' => $success,
            'page_title' => 'Добавление пользователя',
            'return_page' => $return_page,
            'return_per_page' => $return_per_page
        ]);
    }
}
?>