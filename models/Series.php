<?php
// models/Series.php

class Series {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, 
                   COUNT(b.id) as book_count,
                   COALESCE((
                       SELECT SUM(c.word_count) 
                       FROM chapters c 
                       JOIN books b2 ON c.book_id = b2.id 
                       WHERE b2.series_id = s.id AND b2.published = 1
                   ), 0) as total_words
            FROM series s 
            LEFT JOIN books b ON s.id = b.series_id AND b.published = 1
            WHERE s.id = ?
            GROUP BY s.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByUser($user_id, $include_stats = true) {
        if ($include_stats) {
            $sql = "
                SELECT s.*, 
                       COUNT(b.id) as book_count,
                       COALESCE((
                           SELECT SUM(c.word_count) 
                           FROM chapters c 
                           JOIN books b2 ON c.book_id = b2.id 
                           WHERE b2.series_id = s.id AND b2.user_id = ?
                       ), 0) as total_words
                FROM series s 
                LEFT JOIN books b ON s.id = b.series_id
                WHERE s.user_id = ?
                GROUP BY s.id 
                ORDER BY s.created_at DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id, $user_id]);
        } else {
            $sql = "SELECT * FROM series WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user_id]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO series (title, description, user_id) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['user_id']
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE series 
            SET title = ?, description = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $id,
            $data['user_id']
        ]);
    }
    
    public function delete($id, $user_id) {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("UPDATE books SET series_id = NULL, sort_order_in_series = NULL WHERE series_id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            
            $stmt = $this->pdo->prepare("DELETE FROM series WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$id, $user_id]);
            
            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
    public function userOwnsSeries($series_id, $user_id) {
        $stmt = $this->pdo->prepare("SELECT id FROM series WHERE id = ? AND user_id = ?");
        $stmt->execute([$series_id, $user_id]);
        return $stmt->fetch() !== false;
    }
    
    public function getBooksInSeries($series_id, $only_published = false) {
        $sql = "SELECT * FROM books WHERE series_id = ?";
        if ($only_published) {
            $sql .= " AND published = 1";
        }
        $sql .= " ORDER BY sort_order_in_series, created_at";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$series_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getNextSortOrder($series_id) {
        $stmt = $this->pdo->prepare("SELECT MAX(sort_order_in_series) as max_order FROM books WHERE series_id = ?");
        $stmt->execute([$series_id]);
        $result = $stmt->fetch();
        return ($result['max_order'] ?? 0) + 1;
    }
    
    public function getSeriesStats($series_id, $user_id = null) {
        $sql = "
            SELECT 
                COUNT(b.id) as book_count,
                COALESCE(SUM(stats.chapter_count), 0) as chapter_count,
                COALESCE(SUM(stats.total_words), 0) as total_words
            FROM series s
            LEFT JOIN books b ON s.id = b.series_id
            LEFT JOIN (
                SELECT 
                    book_id,
                    COUNT(id) as chapter_count,
                    SUM(word_count) as total_words
                FROM chapters 
                GROUP BY book_id
            ) stats ON b.id = stats.book_id
            WHERE s.id = ?
        ";
        
        $params = [$series_id];
        
        if ($user_id) {
            $sql .= " AND s.user_id = ?";
            $params[] = $user_id;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>