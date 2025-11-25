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
        $users = $userModel->findAll();
        
        $this->render('admin/users', [
            'users' => $users,
            'page_title' => 'Управление пользователями'
        ]);
    }
    
    public function toggleUserStatus($user_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Неверный метод запроса или токен безопасности";
            $this->redirect('/admin/users');
            return;
        }
        
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "Нельзя изменить статус собственного аккаунта";
            $this->redirect('/admin/users');
            return;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($user_id);
        
        if (!$user) {
            $_SESSION['error'] = "Пользователь не найден";
            $this->redirect('/admin/users');
            return;
        }
        
        $newStatus = $user['is_active'] ? 0 : 1;
        if ($userModel->updateStatus($user_id, $newStatus)) {
            $_SESSION['success'] = "Статус пользователя обновлен";
        } else {
            $_SESSION['error'] = "Ошибка при обновлении статуса";
        }
        
        $this->redirect('/admin/users');
    }
    
    public function deleteUser($user_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Неверный метод запроса или токен безопасности";
            $this->redirect('/admin/users');
            return;
        }
        
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "Нельзя удалить собственный аккаунт";
            $this->redirect('/admin/users');
            return;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($user_id);
        
        if (!$user) {
            $_SESSION['error'] = "Пользователь не найден";
            $this->redirect('/admin/users');
            return;
        }
        
        if ($userModel->delete($user_id)) {
            $_SESSION['success'] = "Пользователь успешно удален";
        } else {
            $_SESSION['error'] = "Ошибка при удалении пользователя";
        }
        
        $this->redirect('/admin/users');
    }
    
    public function addUser() {
        $error = '';
        $success = '';
        
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
                            $success = 'Пользователь успешно создан';
                            // Очищаем поля формы
                            $_POST = [];
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
            'page_title' => 'Добавление пользователя'
        ]);
    }
}
?>