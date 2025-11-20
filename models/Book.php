<?php
// models/Book.php

class Book {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByShareToken($share_token) {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE share_token = ?");
        $stmt->execute([$share_token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByUser($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT b.*, 
                   COUNT(c.id) as chapter_count,
                   COALESCE(SUM(c.word_count), 0) as total_words
            FROM books b 
            LEFT JOIN chapters c ON b.id = c.book_id 
            WHERE b.user_id = ? 
            GROUP BY b.id 
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $share_token = bin2hex(random_bytes(16));
        
        $stmt = $this->pdo->prepare("
            INSERT INTO books (title, description, genre, user_id, series_id, sort_order_in_series, share_token) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['genre'] ?? null,
            $data['user_id'],
            $data['series_id'] ?? null,
            $data['sort_order_in_series'] ?? null,
            $share_token
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE books 
            SET title = ?, description = ?, genre = ?, series_id = ?, sort_order_in_series = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['genre'] ?? null,
            $data['series_id'] ?? null,
            $data['sort_order_in_series'] ?? null,
            $id,
            $data['user_id']
        ]);
    }
    
    public function delete($id, $user_id) {
        try {
            $this->pdo->beginTransaction();
            
            // Удаляем главы книги (сработает CASCADE, но лучше явно)
            $stmt = $this->pdo->prepare("DELETE FROM chapters WHERE book_id = ?");
            $stmt->execute([$id]);
            
            // Удаляем саму книгу
            $stmt = $this->pdo->prepare("DELETE FROM books WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$id, $user_id]);
            
            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
    public function userOwnsBook($book_id, $user_id) {
        $stmt = $this->pdo->prepare("SELECT id FROM books WHERE id = ? AND user_id = ?");
        $stmt->execute([$book_id, $user_id]);
        return $stmt->fetch() !== false;
    }
    
    public function generateNewShareToken($book_id) {
        $new_token = bin2hex(random_bytes(16));
        $stmt = $this->pdo->prepare("UPDATE books SET share_token = ? WHERE id = ?");
        $success = $stmt->execute([$new_token, $book_id]);
        return $success ? $new_token : false;
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
}
?>