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
    
    public function findByUser($user_id, $only_published = false) {
        $sql = "
        SELECT b.*,
                COUNT(c.id) as chapter_count,
                COALESCE(SUM(c.word_count), 0) as total_words
        FROM books b
        LEFT JOIN chapters c ON b.id = c.book_id
        WHERE b.user_id = ?
        ";
        if ($only_published) {
            $sql .= " AND b.published = 1 ";
        }
        $sql .= " GROUP BY b.id ORDER BY b.created_at DESC ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $share_token = bin2hex(random_bytes(16));
        $published = isset($data['published']) ? (int)$data['published'] : 0;

        $stmt = $this->pdo->prepare("
            INSERT INTO books (title, description, genre, user_id, series_id, sort_order_in_series, share_token, published)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['genre'] ?? null,
            $data['user_id'],
            $data['series_id'] ?? null,
            $data['sort_order_in_series'] ?? null,
            $share_token,
            $published
        ]);
    }
    
    public function update($id, $data) {
        $published = isset($data['published']) ? (int)$data['published'] : 0;

        $stmt = $this->pdo->prepare("
            UPDATE books
            SET title = ?, description = ?, genre = ?, series_id = ?, sort_order_in_series = ?, published = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['genre'] ?? null,
            $data['series_id'] ?? null,
            $data['sort_order_in_series'] ?? null,
            $published,
            $id,
            $data['user_id']
        ]);
    }
    
    public function delete($id, $user_id) {
        try {
            $this->pdo->beginTransaction();
            
            // Удаляем главы книги 
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

    public function updateCover($book_id, $filename) {
        $stmt = $this->pdo->prepare("UPDATE books SET cover_image = ? WHERE id = ?");
        return $stmt->execute([$filename, $book_id]);
    }

    public function deleteCover($book_id) {

        $book = $this->findById($book_id);
        $old_filename = $book['cover_image'];
        
        if ($old_filename) {
            $file_path = COVERS_PATH . $old_filename;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $stmt = $this->pdo->prepare("UPDATE books SET cover_image = NULL WHERE id = ?");
        return $stmt->execute([$book_id]);
    }

    public function updateSeriesInfo($book_id, $series_id, $sort_order) {
        $stmt = $this->pdo->prepare("UPDATE books SET series_id = ?, sort_order_in_series = ? WHERE id = ?");
        return $stmt->execute([$series_id, $sort_order, $book_id]);
    }

    public function removeFromSeries($book_id) {
        $stmt = $this->pdo->prepare("UPDATE books SET series_id = NULL, sort_order_in_series = NULL WHERE id = ?");
        return $stmt->execute([$book_id]);
    }

   public function findBySeries($series_id) {
        $stmt = $this->pdo->prepare("
            SELECT b.*
            FROM books b
            WHERE b.series_id = ?
            ORDER BY b.sort_order_in_series, b.created_at
        ");
        $stmt->execute([$series_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reorderSeriesBooks($series_id, $new_order) {
        try {
            $this->pdo->beginTransaction();
            
            foreach ($new_order as $order => $book_id) {
                $stmt = $this->pdo->prepare("UPDATE books SET sort_order_in_series = ? WHERE id = ? AND series_id = ?");
                $stmt->execute([$order + 1, $book_id, $series_id]);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }


    public function getBookStats($book_id, $only_published_chapters = false) {
        $sql = "
            SELECT 
                COUNT(c.id) as chapter_count,
                COALESCE(SUM(c.word_count), 0) as total_words
            FROM books b
            LEFT JOIN chapters c ON b.id = c.book_id
            WHERE b.id = ?
        ";
        
        if ($only_published_chapters) {
            $sql .= " AND c.status = 'published'";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$book_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
?>