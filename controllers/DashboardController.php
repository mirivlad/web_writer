<?php
// controllers/DashboardController.php
require_once 'controllers/BaseController.php';
require_once 'models/Book.php';
require_once 'models/Chapter.php';
require_once 'models/Series.php';

class DashboardController extends BaseController {
    
    public function index() {
        $this->requireLogin();
        
        $user_id = $_SESSION['user_id'];
        
        $bookModel = new Book($this->pdo);
        $chapterModel = new Chapter($this->pdo);
        $seriesModel = new Series($this->pdo);
        
        // Получаем статистику
        $books = $bookModel->findByUser($user_id);
        $published_books = $bookModel->findByUser($user_id, true);
        
        $total_books = count($books);
        $published_books_count = count($published_books);
        
        // Общее количество слов и глав
        $total_words = 0;
        $total_chapters = 0;
        foreach ($books as $book) {
            $stats = $bookModel->getBookStats($book['id']);
            $total_words += $stats['total_words'] ?? 0;
            $total_chapters += $stats['chapter_count'] ?? 0;
        }
        
        // Последние книги
        $recent_books = array_slice($books, 0, 5);
        
        // Серии
        $series = $seriesModel->findByUser($user_id);
        
        $this->render('dashboard/index', [
            'total_books' => $total_books,
            'published_books_count' => $published_books_count,
            'total_words' => $total_words,
            'total_chapters' => $total_chapters,
            'recent_books' => $recent_books,
            'series' => $series,
            'page_title' => 'Панель управления'
        ]);
    }
}
?>