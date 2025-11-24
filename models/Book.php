<?php
// models/Book.php
require_once __DIR__ . '/../includes/parsedown/ParsedownExtra.php';
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
        $editor_type = $data['editor_type'] ?? 'markdown';

        $stmt = $this->pdo->prepare("
            INSERT INTO books (title, description, genre, user_id, series_id, sort_order_in_series, share_token, published, editor_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['genre'] ?? null,
            $data['user_id'],
            $data['series_id'] ?? null,
            $data['sort_order_in_series'] ?? null,
            $share_token,
            $published,
            $editor_type
        ]);
    }
    
    public function update($id, $data) {
        $published = isset($data['published']) ? (int)$data['published'] : 0;
        $editor_type = $data['editor_type'] ?? 'markdown';

        $stmt = $this->pdo->prepare("
            UPDATE books
            SET title = ?, description = ?, genre = ?, series_id = ?, sort_order_in_series = ?, published = ?, editor_type = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            $data['genre'] ?? null,
            $data['series_id'] ?? null,
            $data['sort_order_in_series'] ?? null,
            $published,
            $editor_type,
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
    
    public function convertChaptersContent($book_id, $from_editor, $to_editor) {
        try {
            $this->pdo->beginTransaction();
            
            $chapters = $this->getAllChapters($book_id);
            
            foreach ($chapters as $chapter) {
                $converted_content = $this->convertContent(
                    $chapter['content'],
                    $from_editor,
                    $to_editor
                );
                
                $this->updateChapterContent($chapter['id'], $converted_content);
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error converting chapters: " . $e->getMessage());
            return false;
        }
    }

    private function getAllChapters($book_id) {
        $stmt = $this->pdo->prepare("SELECT id, content FROM chapters WHERE book_id = ?");
        $stmt->execute([$book_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function updateChapterContent($chapter_id, $content) {
        $word_count = $this->countWords($content);
        $stmt = $this->pdo->prepare("
            UPDATE chapters 
            SET content = ?, word_count = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$content, $word_count, $chapter_id]);
    }
    
    private function countWords($text) {
        $text = strip_tags($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $words = preg_split('/\s+/', $text);
        $words = array_filter($words);
        return count($words);
    }
    
    private function convertContent($content, $from_editor, $to_editor) {
        if ($from_editor === $to_editor) {
            return $content;
        }
        
        try {
            if ($from_editor === 'markdown' && $to_editor === 'html') {
                // Markdown to HTML с улучшенной обработкой абзацев
                return $this->markdownToHtmlWithParagraphs($content);
            } elseif ($from_editor === 'html' && $to_editor === 'markdown') {
                // HTML to Markdown
                return $this->htmlToMarkdown($content);
            }
        } catch (Exception $e) {
            error_log("Error converting content from {$from_editor} to {$to_editor}: " . $e->getMessage());
            return $content;
        }
        
        return $content;
    }

    private function markdownToHtmlWithParagraphs($markdown) {
        $parsedown = new ParsedownExtra();
        
        // Включаем разметку строк для лучшей обработки абзацев
        $parsedown->setBreaksEnabled(true);
        
        // Обрабатываем Markdown
        $html = $parsedown->text($markdown);
        
        // Дополнительная обработка для обеспечения правильной структуры абзацев
        $html = $this->ensureParagraphStructure($html);
        
        return $html;
    }

    private function ensureParagraphStructure($html) {
        // Если HTML не содержит тегов абзацев или div'ов, оборачиваем в <p>
        if (!preg_match('/<(p|div|h[1-6]|blockquote|pre|ul|ol|li)/i', $html)) {
            // Разбиваем на строки и оборачиваем каждую непустую строку в <p>
            $lines = explode("\n", trim($html));
            $wrappedLines = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    // Пропускаем уже обернутые строки
                    if (!preg_match('/^<[^>]+>/', $line) || preg_match('/^<(p|div|h[1-6])/i', $line)) {
                        $wrappedLines[] = $line;
                    } else {
                        $wrappedLines[] = "<p>{$line}</p>";
                    }
                }
            }
            
            $html = implode("\n", $wrappedLines);
        }
        
        // Убеждаемся, что теги правильно закрыты
        $html = $this->balanceTags($html);
        
        return $html;
    }

    private function balanceTags($html) {
        // Простая балансировка тегов - в реальном проекте лучше использовать DOMDocument
        $tags = [
            'p' => 0,
            'div' => 0,
            'span' => 0,
            'strong' => 0,
            'em' => 0,
        ];
        
        // Счетчик открывающих и закрывающих тегов
        foreach ($tags as $tag => &$count) {
            $open = substr_count($html, "<{$tag}>") + substr_count($html, "<{$tag} ");
            $close = substr_count($html, "</{$tag}>");
            $count = $open - $close;
        }
        
        // Добавляем недостающие закрывающие теги
        foreach ($tags as $tag => $count) {
            if ($count > 0) {
                $html .= str_repeat("</{$tag}>", $count);
            }
        }
        
        return $html;
    }
    private function htmlToMarkdown($html) {
        // Сначала нормализуем HTML структуру
        $html = $this->normalizeHtml($html);
        
        // Базовая конвертация HTML в Markdown
        $markdown = $html;
        
        // Обрабатываем абзацы - заменяем на двойные переносы строк
        $markdown = preg_replace('/<p[^>]*>(.*?)<\/p>/is', "$1\n\n", $markdown);
        
        // Обрабатываем разрывы строк
        $markdown = preg_replace('/<br[^>]*>\s*<\/br[^>]*>/i', "\n", $markdown);
        $markdown = preg_replace('/<br[^>]*>/i', "  \n", $markdown); // Два пробела для Markdown разрыва
        
        // Заголовки
        $markdown = preg_replace('/<h1[^>]*>(.*?)<\/h1>/is', "# $1\n\n", $markdown);
        $markdown = preg_replace('/<h2[^>]*>(.*?)<\/h2>/is', "## $1\n\n", $markdown);
        $markdown = preg_replace('/<h3[^>]*>(.*?)<\/h3>/is', "### $1\n\n", $markdown);
        $markdown = preg_replace('/<h4[^>]*>(.*?)<\/h4>/is', "#### $1\n\n", $markdown);
        $markdown = preg_replace('/<h5[^>]*>(.*?)<\/h5>/is', "##### $1\n\n", $markdown);
        $markdown = preg_replace('/<h6[^>]*>(.*?)<\/h6>/is', "###### $1\n\n", $markdown);
        
        // Жирный текст
        $markdown = preg_replace('/<strong[^>]*>(.*?)<\/strong>/is', '**$1**', $markdown);
        $markdown = preg_replace('/<b[^>]*>(.*?)<\/b>/is', '**$1**', $markdown);
        
        // Курсив
        $markdown = preg_replace('/<em[^>]*>(.*?)<\/em>/is', '*$1*', $markdown);
        $markdown = preg_replace('/<i[^>]*>(.*?)<\/i>/is', '*$1*', $markdown);
        
        // Подчеркивание (не стандартно в Markdown, но обрабатываем)
        $markdown = preg_replace('/<u[^>]*>(.*?)<\/u>/is', '<u>$1</u>', $markdown);
        
        // Зачеркивание
        $markdown = preg_replace('/<s[^>]*>(.*?)<\/s>/is', '~~$1~~', $markdown);
        $markdown = preg_replace('/<strike[^>]*>(.*?)<\/strike>/is', '~~$1~~', $markdown);
        $markdown = preg_replace('/<del[^>]*>(.*?)<\/del>/is', '~~$1~~', $markdown);
        
        // Списки
        $markdown = preg_replace('/<li[^>]*>(.*?)<\/li>/is', '- $1', $markdown);
        $markdown = preg_replace('/<ul[^>]*>(.*?)<\/ul>/is', "$1\n", $markdown);
        $markdown = preg_replace('/<ol[^>]*>(.*?)<\/ol>/is', "$1\n", $markdown);
        
        // Блочные цитаты
        $markdown = preg_replace('/<blockquote[^>]*>(.*?)<\/blockquote>/is', "> $1\n", $markdown);
        
        // Код
        $markdown = preg_replace('/<code[^>]*>(.*?)<\/code>/is', '`$1`', $markdown);
        $markdown = preg_replace('/<pre[^>]*>(.*?)<\/pre>/is', "```\n$1\n```", $markdown);
        
        // Ссылки
        $markdown = preg_replace('/<a[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/is', '[$2]($1)', $markdown);
        
        // Изображения
        $markdown = preg_replace('/<img[^>]*src="([^"]*)"[^>]*alt="([^"]*)"[^>]*>/is', '![$2]($1)', $markdown);
        
        // Удаляем все остальные HTML-теги
        $markdown = strip_tags($markdown);
        
        // Чистим лишние пробелы и переносы
        $markdown = preg_replace('/\n\s*\n\s*\n/', "\n\n", $markdown);
        $markdown = preg_replace('/^\s+|\s+$/m', '', $markdown); // Trim каждой строки
        $markdown = trim($markdown);
        
        return $markdown;
    }

    private function normalizeHtml($html) {
        // Нормализуем HTML структуру перед конвертацией
        $html = preg_replace('/<div[^>]*>(.*?)<\/div>/is', "<p>$1</p>", $html);
        
        // Убираем лишние пробелы
        $html = preg_replace('/\s+/', ' ', $html);
        
        // Восстанавливаем структуру абзацев
        $html = preg_replace('/([^>])\s*<\/(p|div)>\s*([^<])/', "$1</$2>\n\n$3", $html);
        
        return $html;
    }

    public function normalizeBookContent($book_id) {
        try {
            $chapters = $this->getAllChapters($book_id);
            $book = $this->findById($book_id);
            
            foreach ($chapters as $chapter) {
                $normalized_content = '';
                
                if ($book['editor_type'] == 'html') {
                    // Нормализуем HTML контент
                    $normalized_content = $this->normalizeHtmlContent($chapter['content']);
                } else {
                    // Нормализуем Markdown контент
                    $normalized_content = $this->normalizeMarkdownContent($chapter['content']);
                }
                
                if ($normalized_content !== $chapter['content']) {
                    $this->updateChapterContent($chapter['id'], $normalized_content);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error normalizing book content: " . $e->getMessage());
            return false;
        }
    }

    private function normalizeHtmlContent($html) {
        // Простая нормализация HTML - оборачиваем текст без тегов в <p>
        if (!preg_match('/<[^>]+>/', $html) && trim($html) !== '') {
            // Если нет HTML тегов, оборачиваем в <p>
            $lines = explode("\n", trim($html));
            $wrapped = array_map(function($line) {
                $line = trim($line);
                return $line ? "<p>{$line}</p>" : '';
            }, $lines);
            return implode("\n", array_filter($wrapped));
        }
        
        return $html;
    }

    private function normalizeMarkdownContent($markdown) {
        // Нормализация Markdown - убеждаемся, что есть пустые строки между абзацами
        $lines = explode("\n", $markdown);
        $normalized = [];
        $inParagraph = false;
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            if (empty($trimmed)) {
                // Пустая строка - конец абзаца
                if ($inParagraph) {
                    $normalized[] = '';
                    $inParagraph = false;
                }
            } else {
                // Непустая строка
                if (!$inParagraph && !empty($normalized) && end($normalized) !== '') {
                    // Добавляем пустую строку перед новым абзацем
                    $normalized[] = '';
                }
                $normalized[] = $line;
                $inParagraph = true;
            }
        }
        
        return implode("\n", $normalized);
    }
}
?>