<?php
// controllers/BaseController.php
class BaseController {
    protected $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    protected function render($view, $data = []) {
        extract($data);
        include "views/$view.php";
    }
    
    protected function redirect($url) {
        header("Location: " . SITE_URL . $url);
        exit;
    }
    
    protected function requireLogin() {
        if (!is_logged_in()) {
            $this->redirect('/login');
        }
    }
    
    protected function requireAdmin() {
        if (!is_logged_in()) {
            $this->redirect('/login');
            return;
        }
        
        global $pdo;
        $userModel = new User($pdo);
        $user = $userModel->findById($_SESSION['user_id']);
        
        if (!$user || $user['id'] != 1) { // Предполагаем, что администратор имеет ID = 1
            $_SESSION['error'] = "У вас нет прав администратора";
            $this->redirect('/dashboard');
            exit;
        }
    }

    protected function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>