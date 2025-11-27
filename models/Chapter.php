<?php
// models/Chapter.php

class Chapter {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, b.user_id, b.title as book_title 
            FROM chapters c 
            JOIN books b ON c.book_id = b.id 
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByBook($book_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM chapters WHERE book_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$book_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        // Получаем максимальный порядковый номер для этой книги
        $stmt = $this->pdo->prepare("SELECT MAX(sort_order) as max_order FROM chapters WHERE book_id = ?");
        $stmt->execute([$data['book_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_order = ($result['max_order'] ?? 0) + 1;

        $stmt = $this->pdo->prepare("
            INSERT INTO chapters (book_id, title, content, word_count, sort_order, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $word_count = str_word_count(strip_tags($data['content']));

        return $stmt->execute([
            $data['book_id'],
            $data['title'],
            $data['content'],
            $word_count,
            $next_order,
            $data['status'] ?? 'draft'
        ]);
    }
    
    public function update($id, $data) {
        $word_count = $this->countWords($data['content']);
        
        $stmt = $this->pdo->prepare("
            UPDATE chapters 
            SET title = ?, content = ?, word_count = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['content'],
            $word_count,
            $data['status'] ?? 'draft',
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM chapters WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function updateSortOrder($chapter_id, $new_order) {
        $stmt = $this->pdo->prepare("UPDATE chapters SET sort_order = ? WHERE id = ?");
        return $stmt->execute([$new_order, $chapter_id]);
    }
    
    private function countWords($text) {
        $text = strip_tags($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $words = preg_split('/\s+/', $text);
        $words = array_filter($words);
        return count($words);
    }
    
    public function userOwnsChapter($chapter_id, $user_id) {
        $stmt = $this->pdo->prepare("
            SELECT c.id 
            FROM chapters c 
            JOIN books b ON c.book_id = b.id 
            WHERE c.id = ? AND b.user_id = ?
        ");
        $stmt->execute([$chapter_id, $user_id]);
        return $stmt->fetch() !== false;
    }

        
    public function getPublishedChapters($book_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM chapters 
            WHERE book_id = ? AND status = 'published' 
            ORDER BY sort_order, created_at
        ");
        $stmt->execute([$book_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateChaptersOrder($book_id, $chapter_ids) {
        try {
            $this->pdo->beginTransaction();
            
            // Обновляем порядок для каждой главы
            foreach ($chapter_ids as $index => $chapter_id) {
                $stmt = $this->pdo->prepare("UPDATE chapters SET sort_order = ? WHERE id = ? AND book_id = ?");
                $stmt->execute([$index + 1, $chapter_id, $book_id]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error updating chapters order: " . $e->getMessage());
            return false;
        }
    }

}
?>