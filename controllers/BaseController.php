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
    
    protected function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>