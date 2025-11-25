<?php
// controllers/AuthController.php
require_once 'controllers/BaseController.php';
require_once 'models/User.php';

class AuthController extends BaseController {
    
    public function login() {
        // Если пользователь уже авторизован, перенаправляем на dashboard
        if (is_logged_in()) {
            $this->redirect('/dashboard');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                $error = "Ошибка безопасности";
            } else {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                if (empty($username) || empty($password)) {
                    $error = 'Пожалуйста, введите имя пользователя и пароль';
                } else {
                    $userModel = new User($this->pdo);
                    $user = $userModel->findByUsername($username);

                    if ($user && $userModel->verifyPassword($password, $user['password_hash'])) {
                        if (!$user['is_active']) {
                            $error = 'Ваш аккаунт деактивирован или ожидает активации администратором.';
                        } else {
                            // Успешный вход
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['display_name'] = $user['display_name'] ?: $user['username'];
                            $_SESSION['avatar'] = $user['avatar'] ?? null;
                            
                            // Обновляем время последнего входа
                            $userModel->updateLastLogin($user['id']);
                            
                            $_SESSION['success'] = 'Добро пожаловать, ' . e($user['display_name'] ?: $user['username']) . '!';
                            $this->redirect('/dashboard');
                        }
                    } else {
                        $error = 'Неверное имя пользователя или пароль';
                    }
                }
            }
        }

        $this->render('auth/login', [
            'error' => $error,
            'page_title' => 'Вход в систему'
        ]);
    }
    
    public function logout() {
        // Очищаем все данные сессии
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        $this->redirect('/login');
    }
    
    public function register() {
        // Если пользователь уже авторизован, перенаправляем на dashboard
        if (is_logged_in()) {
            $this->redirect('/dashboard');
        }

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

                // Валидация
                if (empty($username) || empty($password)) {
                    $error = 'Имя пользователя и пароль обязательны';
                } elseif ($password !== $password_confirm) {
                    $error = 'Пароли не совпадают';
                } elseif (strlen($password) < 6) {
                    $error = 'Пароль должен быть не менее 6 символов';
                } else {
                    $userModel = new User($this->pdo);
                    
                    // Проверяем, не занят ли username
                    if ($userModel->findByUsername($username)) {
                        $error = 'Имя пользователя уже занято';
                    } elseif ($email && $userModel->findByEmail($email)) {
                        $error = 'Email уже используется';
                    } else {
                        $data = [
                            'username' => $username,
                            'password' => $password,
                            'email' => $email ?: null,
                            'display_name' => $display_name ?: $username,
                            'is_active' => 1 // Авто-активация для простоты
                        ];
                        
                        if ($userModel->create($data)) {
                            $success = 'Регистрация успешна! Теперь вы можете войти в систему.';
                            // Можно автоматически войти после регистрации
                            // $this->redirect('/login');
                        } else {
                            $error = 'Ошибка при создании аккаунта';
                        }
                    }
                }
            }
        }

        $this->render('auth/register', [
            'error' => $error,
            'success' => $success,
            'page_title' => 'Регистрация'
        ]);
    }
}
?>