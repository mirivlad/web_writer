<?php
require_once 'config/config.php';
require_once 'vendor/autoload.php';
require_once 'includes/parsedown/ParsedownExtra.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use TCPDF;

// Проверяем авторизацию или share_token
$user_id = $_SESSION['user_id'] ?? null;
$share_token = $_GET['share_token'] ?? null;
$book_id = $_GET['book_id'] ?? null;
$format = $_GET['format'] ?? 'pdf';

if (!$user_id && !$share_token) {
    $_SESSION['error'] = "Доступ запрещен";
    redirect('login.php');
}

$bookModel = new Book($pdo);
$chapterModel = new Chapter($pdo);
$Parsedown = new ParsedownExtra();

// Получаем книгу
if ($share_token) {
    $book = $bookModel->findByShareToken($share_token);
    // Для публичного доступа - только опубликованные главы
    $chapters = $bookModel->getPublishedChapters($book['id']);
    $is_public = true;
} elseif ($book_id && $user_id) {
    $book = $bookModel->findById($book_id);
    if (!$book || $book['user_id'] != $user_id) {
        $_SESSION['error'] = "Доступ запрещен";
        redirect('books.php');
    }
    // Для автора - все главы
    $chapters = $chapterModel->findByBook($book_id);
    $is_public = false;
} else {
    $_SESSION['error'] = "Книга не найдена";
    redirect('books.php');
}

if (!$book) {
    $_SESSION['error'] = "Книга не найдена";
    redirect('books.php');
}
// Получаем информацию об авторе
$author_info = null;
if ($book) {
    $stmt = $pdo->prepare("SELECT display_name, username FROM users WHERE id = ?");
    $stmt->execute([$book['user_id']]);
    $author_info = $stmt->fetch(PDO::FETCH_ASSOC);
}

$author_name = $author_info['display_name'] ?? $author_info['username'] ?? 'Неизвестный автор';


// Функция для преобразования Markdown в чистый текст с форматированием абзацев
function markdownToPlainText($markdown) {
    // Обрабатываем диалоги (заменяем - на —)
    $markdown = preg_replace('/^- (.+)$/m', "— $1", $markdown);
    
    // Убираем Markdown разметку, но сохраняем переносы строк
    $text = $markdown;
    
    // Убираем заголовки
    $text = preg_replace('/^#+\s+/m', '', $text);
    
    // Убираем жирный и курсив
    $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
    $text = preg_replace('/\*(.*?)\*/', '$1', $text);
    $text = preg_replace('/__(.*?)__/', '$1', $text);
    $text = preg_replace('/_(.*?)_/', '$1', $text);
    
    // Убираем зачеркивание
    $text = preg_replace('/~~(.*?)~~/', '$1', $text);
    
    // Убираем код (встроенный)
    $text = preg_replace('/`(.*?)`/', '$1', $text);
    
    // Убираем блоки кода (сохраняем содержимое)
    $text = preg_replace('/```.*?\n(.*?)```/s', '$1', $text);
    
    // Убираем ссылки
    $text = preg_replace('/\[(.*?)\]\(.*?\)/', '$1', $text);
    
    // Обрабатываем списки - заменяем маркеры на *
    $text = preg_replace('/^[\*\-+]\s+/m', '* ', $text);
    $text = preg_replace('/^\d+\.\s+/m', '* ', $text);
    
    // Обрабатываем цитаты
    $text = preg_replace('/^>\s+/m', '', $text);
    
    return $text;
}
// Функция для разбивки Markdown на абзацы с сохранением структуры
function markdownToParagraphs($markdown) {
    // Нормализуем переносы строк
    $text = str_replace(["\r\n", "\r"], "\n", $markdown);
    
    // Обрабатываем диалоги (заменяем - на —)
    $text = preg_replace('/^- (.+)$/m', "— $1", $text);
    
    // Разбиваем на строки
    $lines = explode("\n", $text);
    $paragraphs = [];
    $currentParagraph = '';
    
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        
        // Пустая строка - конец абзаца
        if (empty($trimmedLine)) {
            if (!empty($currentParagraph)) {
                $paragraphs[] = $currentParagraph;
                $currentParagraph = '';
            }
            continue;
        }
        
        // Диалог (начинается с —) всегда начинает новый абзац
        if (str_starts_with($trimmedLine, '—')) {
            if (!empty($currentParagraph)) {
                $paragraphs[] = $currentParagraph;
            }
            $currentParagraph = $trimmedLine;
            $paragraphs[] = $currentParagraph;
            $currentParagraph = '';
            continue;
        }
        
        // Заголовки (начинаются с #) всегда начинают новый абзац
        if (str_starts_with($trimmedLine, '#')) {
            if (!empty($currentParagraph)) {
                $paragraphs[] = $currentParagraph;
            }
            $currentParagraph = preg_replace('/^#+\s+/', '', $trimmedLine);
            $paragraphs[] = $currentParagraph;
            $currentParagraph = '';
            continue;
        }
        
        // Обычный текст - добавляем к текущему абзацу
        if (!empty($currentParagraph)) {
            $currentParagraph .= ' ' . $trimmedLine;
        } else {
            $currentParagraph = $trimmedLine;
        }
    }
    
    // Добавляем последний абзац
    if (!empty($currentParagraph)) {
        $paragraphs[] = $currentParagraph;
    }
    
    return $paragraphs;
}

// Функция для очистки Markdown разметки
function cleanMarkdown($markdown) {
    $text = $markdown;
    
    // Убираем заголовки
    $text = preg_replace('/^#+\s+/m', '', $text);
    
    // Убираем жирный и курсив
    $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
    $text = preg_replace('/\*(.*?)\*/', '$1', $text);
    $text = preg_replace('/__(.*?)__/', '$1', $text);
    $text = preg_replace('/_(.*?)_/', '$1', $text);
    
    // Убираем зачеркивание
    $text = preg_replace('/~~(.*?)~~/', '$1', $text);
    
    // Убираем код
    $text = preg_replace('/`(.*?)`/', '$1', $text);
    $text = preg_replace('/```.*?\n(.*?)```/s', '$1', $text);
    
    // Убираем ссылки
    $text = preg_replace('/\[(.*?)\]\(.*?\)/', '$1', $text);
    
    // Обрабатываем списки - убираем маркеры
    $text = preg_replace('/^[\*\-+]\s+/m', '', $text);
    $text = preg_replace('/^\d+\.\s+/m', '', $text);
    
    // Обрабатываем цитаты
    $text = preg_replace('/^>\s+/m', '', $text);
    
    return $text;
}

// Функция для форматирования текста с сохранением абзацев и диалогов
function formatPlainText($text) {
    $lines = explode("\n", $text);
    $formatted = [];
    $in_paragraph = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (empty($line)) {
            if ($in_paragraph) {
                $formatted[] = ''; // Пустая строка для разделения абзацев
                $in_paragraph = false;
            }
            continue;
        }
        
        // Диалоги начинаются с —
        if (str_starts_with($line, '—')) {
            if ($in_paragraph) {
                $formatted[] = ''; // Разделяем абзацы перед диалогом
            }
            $formatted[] = $line;
            $formatted[] = ''; // Пустая строка после диалога
            $in_paragraph = false;
        } else {
            // Обычный текст
            $formatted[] = $line;
            $in_paragraph = true;
        }
    }
    
    return implode("\n", array_filter($formatted, function($line) {
        return $line !== '' || !empty($line);
    }));
}

// Обработка экспорта
switch ($format) {
    case 'pdf':
        exportPDF($book, $chapters, $is_public, $author_name);
        break;
    case 'docx':
        exportDOCX($book, $chapters, $is_public, $author_name);
        break;
    case 'html':
        exportHTML($book, $chapters, $is_public, $author_name);
        break;
    case 'txt':
        exportTXT($book, $chapters, $is_public, $author_name);
        break;
    default:
        $_SESSION['error'] = "Неверный формат экспорта";
        redirect($share_token ? "view_book.php?share_token=$share_token" : "book_edit.php?id=$book_id");
}

function exportPDF($book, $chapters, $is_public, $author_name) {
    global $Parsedown;
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Устанавливаем метаданные документа
    $pdf->SetCreator(APP_NAME);
    $pdf->SetAuthor($author_name);
    $pdf->SetTitle($book['title']);
    $pdf->SetSubject($book['genre'] ?? '');
    
    // Устанавливаем margins
    $pdf->SetMargins(15, 25, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);
    
    // Устанавливаем авто разрыв страниц
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Добавляем страницу
    $pdf->AddPage();
    
    // Устанавливаем шрифт с поддержкой кириллицы
    $pdf->SetFont('dejavusans', '', 12);
    
    // Заголовок книги
    $pdf->SetFont('dejavusans', 'B', 18);
    $pdf->Cell(0, 10, $book['title'], 0, 1, 'C');
    $pdf->Ln(2);
    
    // Автор
    $pdf->SetFont('dejavusans', 'I', 14);
    $pdf->Cell(0, 10, $author_name, 0, 1, 'C');
    $pdf->Ln(5);
    
    // Обложка книги
    if (!empty($book['cover_image'])) {
        $cover_path = COVERS_PATH . $book['cover_image'];
        if (file_exists($cover_path)) {
            list($width, $height) = getimagesize($cover_path);
            $max_width = 80;
            $ratio = $width / $height;
            $new_height = $max_width / $ratio;
            
            $x = (210 - $max_width) / 2;
            $pdf->Image($cover_path, $x, $pdf->GetY(), $max_width, $new_height, '', '', 'N', false, 300, '', false, false, 0, false, false, false);
            $pdf->Ln($new_height + 5);
        }
    }
    
    // Жанр
    if (!empty($book['genre'])) {
        $pdf->SetFont('dejavusans', 'I', 12);
        $pdf->Cell(0, 10, 'Жанр: ' . $book['genre'], 0, 1, 'C');
        $pdf->Ln(5);
    }
    
    // Описание
    if (!empty($book['description'])) {
        $pdf->SetFont('dejavusans', '', 11);
        $pdf->MultiCell(0, 6, $book['description'], 0, 'J');
        $pdf->Ln(10);
    }
    
    // Интерактивное оглавление
    $chapterLinks = [];
    if (!empty($chapters)) {
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 10, 'Оглавление', 0, 1, 'C');
        $pdf->Ln(5);
        
        $toc_page = $pdf->getPage();
        
        $pdf->SetFont('dejavusans', '', 11);
        foreach ($chapters as $index => $chapter) {
            $chapter_number = $index + 1;
            $link = $pdf->AddLink();
            $chapterLinks[$chapter['id']] = $link; // Сохраняем ссылку для этой главы
            $pdf->Cell(0, 6, "{$chapter_number}. {$chapter['title']}", 0, 1, 'L', false, $link);
        }
        $pdf->Ln(10);
    }
    
    // Разделитель
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(10);
    
    // Главы с закладками и правильными ссылками
    foreach ($chapters as $index => $chapter) {
        // Добавляем новую страницу для каждой главы
        $pdf->AddPage();
        
        // УСТАНАВЛИВАЕМ ЯКОРЬ ДЛЯ ССЫЛКИ
        if (isset($chapterLinks[$chapter['id']])) {
            $pdf->SetLink($chapterLinks[$chapter['id']]);
        }
        
        // Устанавливаем закладку для этой главы
        $pdf->Bookmark($chapter['title'], 0, 0, '', 'B', array(0, 0, 0));
        
        // Название главы
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 8, $chapter['title'], 0, 1);
        $pdf->Ln(2);
        
        // Контент главы
        $pdf->SetFont('dejavusans', '', 11);
        $htmlContent = $Parsedown->text($chapter['content']);
        $pdf->writeHTML($htmlContent, true, false, true, false, '');
        
        $pdf->Ln(8);
    }
    
    // Футер с информацией
    $pdf->SetY(-25);
    $pdf->SetFont('dejavusans', 'I', 8);
    $pdf->Cell(0, 6, 'Экспортировано из ' . APP_NAME . ' - ' . date('d.m.Y H:i'), 0, 1, 'C');
    $pdf->Cell(0, 6, 'Автор: ' . $author_name . ' | Всего глав: ' . count($chapters) . ' | Всего слов: ' . array_sum(array_column($chapters, 'word_count')), 0, 1, 'C');
    
    // Отправляем файл
    $filename = cleanFilename($book['title']) . '.pdf';
    $pdf->Output($filename, 'D');
    exit;
}

function exportDOCX($book, $chapters, $is_public, $author_name) {
    global $Parsedown;
    
    $phpWord = new PhpWord();
    
    // Стили документа
    $phpWord->setDefaultFontName('Times New Roman');
    $phpWord->setDefaultFontSize(12);
    
    // Секция документа
    $section = $phpWord->addSection();
    
    // Заголовок книги
    $section->addText($book['title'], ['bold' => true, 'size' => 16], ['alignment' => 'center']);
    $section->addTextBreak(1);
    
    // Автор
    $section->addText($author_name, ['italic' => true, 'size' => 14], ['alignment' => 'center']);
    $section->addTextBreak(2);
    
    // Обложка книги
    if (!empty($book['cover_image'])) {
        $cover_path = COVERS_PATH . $book['cover_image'];
        if (file_exists($cover_path)) {
            $section->addImage($cover_path, [
                'width' => 150,
                'height' => 225,
                'alignment' => 'center'
            ]);
            $section->addTextBreak(2);
        }
    }
    
    // Жанр
    if (!empty($book['genre'])) {
        $section->addText('Жанр: ' . $book['genre'], ['italic' => true], ['alignment' => 'center']);
        $section->addTextBreak(1);
    }
    
    // Описание
    if (!empty($book['description'])) {
        $descriptionParagraphs = markdownToParagraphs($book['description']);
        foreach ($descriptionParagraphs as $paragraph) {
            $section->addText($paragraph);
        }
        $section->addTextBreak(2);
    }
    
    // Интерактивное оглавление
    if (!empty($chapters)) {
        $section->addText('Оглавление', ['bold' => true, 'size' => 14], ['alignment' => 'center']);
        $section->addTextBreak(1);
        
        foreach ($chapters as $index => $chapter) {
            $chapter_number = $index + 1;
            // Создаем гиперссылку на заголовок главы
            $section->addLink("chapter_{$chapter['id']}", "{$chapter_number}. {$chapter['title']}", null, null, true);
            $section->addTextBreak(1);
        }
        $section->addTextBreak(2);
    }
    
    // Разделитель
    $section->addPageBreak();
    
    // Главы с закладками
    foreach ($chapters as $index => $chapter) {
        // Добавляем закладку для главы
        $section->addBookmark("chapter_{$chapter['id']}");
        
        // Заголовок главы
        $section->addText($chapter['title'], ['bold' => true, 'size' => 14]);
        $section->addTextBreak(1);
        
        // Получаем очищенный текст и разбиваем на абзацы
        $cleanContent = cleanMarkdown($chapter['content']);
        $paragraphs = markdownToParagraphs($cleanContent);
        
        // Добавляем каждый абзац
        foreach ($paragraphs as $paragraph) {
            if (!empty(trim($paragraph))) {
                $section->addText($paragraph);
                $section->addTextBreak(1);
            }
        }
        
        // Добавляем разрыв страницы между главами (кроме последней)
        if ($index < count($chapters) - 1) {
            $section->addPageBreak();
        }
    }
    
    // Футер
    $section->addTextBreak(2);
    $section->addText('Экспортировано из ' . APP_NAME . ' - ' . date('d.m.Y H:i'), ['italic' => true, 'size' => 9]);
    $section->addText('Автор: ' . $author_name . ' | Всего глав: ' . count($chapters) . ' | Всего слов: ' . array_sum(array_column($chapters, 'word_count')), ['italic' => true, 'size' => 9]);
    
    // Сохраняем и отправляем
    $filename = cleanFilename($book['title']) . '.docx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
    exit;
}

function exportHTML($book, $chapters, $is_public, $author_name) {
    global $Parsedown;
    
    $html = '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($book['title']) . '</title>
        <style>
            body { 
                font-family: "Times New Roman", serif; 
                line-height: 1.6; 
                margin: 40px; 
                max-width: 900px;
                margin-left: auto;
                margin-right: auto;
                color: #333;
            }
            .book-title { 
                text-align: center; 
                font-size: 24px; 
                font-weight: bold;
                margin-bottom: 5px;
            }
            .book-author {
                text-align: center;
                font-size: 18px;
                font-style: italic;
                color: #666;
                margin-bottom: 20px;
            }
            .book-cover {
                text-align: center;
                margin: 20px 0;
            }
            .book-cover img {
                max-width: 200px;
                height: auto;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .book-genre { 
                text-align: center; 
                font-style: italic; 
                color: #666;
                margin-bottom: 20px;
            }
            .book-description {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 5px;
                margin: 20px 0;
                border-left: 4px solid #007bff;
            }
            .table-of-contents {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 5px;
                margin: 20px 0;
                columns: 2;
                column-gap: 2rem;
            }
            .table-of-contents h3 {
                margin-top: 0;
                text-align: center;
                column-span: all;
            }
            .table-of-contents ul {
                list-style-type: none;
                padding-left: 0;
            }
            .table-of-contents li {
                margin-bottom: 5px;
                padding: 5px 0;
                break-inside: avoid;
            }
            .table-of-contents a {
                text-decoration: none;
                color: #333;
            }
            .table-of-contents a:hover {
                color: #007bff;
            }
            .chapter-title {
                border-bottom: 2px solid #007bff;
                padding-bottom: 10px;
                margin-top: 30px;
                font-size: 20px;
                scroll-margin-top: 2rem;
            }
            .chapter-content {
                margin: 20px 0;
                text-align: justify;
            }
            .footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                text-align: center;
                font-size: 12px;
                color: #666;
            }
            /* Отображение абзацев */
            .chapter-content p {
                margin-bottom: 1em;
                text-align: justify;
            }
            .dialogue {
                margin-left: 2rem;
                font-style: italic;
                color: #2c5aa0;
                margin-bottom: 1em;
            }
            /* Остальные стили */
            .chapter-content h1, .chapter-content h2, .chapter-content h3 {
                margin-top: 1.5em;
                margin-bottom: 0.5em;
            }
            .chapter-content blockquote {
                border-left: 4px solid #007bff;
                padding-left: 15px;
                margin-left: 0;
                color: #555;
                font-style: italic;
            }
            .chapter-content code {
                background: #f5f5f5;
                padding: 2px 4px;
                border-radius: 3px;
            }
            .chapter-content pre {
                background: #f5f5f5;
                padding: 1rem;
                border-radius: 5px;
                overflow-x: auto;
            }
            .chapter-content ul, .chapter-content ol {
                margin-bottom: 1rem;
                padding-left: 2rem;
            }
            .chapter-content table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 1rem;
            }
            .chapter-content th, .chapter-content td {
                border: 1px solid #ddd;
                padding: 8px 12px;
                text-align: left;
            }
            .chapter-content th {
                background: #f5f5f5;
            }
            @media (max-width: 768px) {
                .table-of-contents {
                    columns: 1;
                }
            }
        </style>
    </head>
    <body>
        <div class="book-title">' . htmlspecialchars($book['title']) . '</div>
        <div class="book-author">' . htmlspecialchars($author_name) . '</div>';
        
        if (!empty($book['genre'])) {
            $html .= '<div class="book-genre">Жанр: ' . htmlspecialchars($book['genre']) . '</div>';
        }
        
        // Обложка книги
        if (!empty($book['cover_image'])) {
            $cover_url = COVERS_URL . $book['cover_image'];
            $html .= '<div class="book-cover">';
            $html .= '<img src="' . $cover_url . '" alt="' . htmlspecialchars($book['title']) . '">';
            $html .= '</div>';
        }
        
        if (!empty($book['description'])) {
            $html .= '<div class="book-description">' . nl2br(htmlspecialchars($book['description'])) . '</div>';
        }
        
        // Интерактивное оглавление
        if (!empty($chapters)) {
            $html .= '<div>';
            $html .= '<h3>Оглавление</h3>';
            $html .= '<ul>';
            foreach ($chapters as $index => $chapter) {
                $chapter_number = $index + 1;
                $html .= '<li><a href="#chapter-' . $chapter['id'] . '">' . $chapter_number . '. ' . htmlspecialchars($chapter['title']) . '</a></li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        
        $html .= '<hr style="margin: 30px 0;">';
        
        foreach ($chapters as $index => $chapter) {
            $html .= '<div class="chapter">';
            $html .= '<div class="chapter-title" id="chapter-' . $chapter['id'] . '" name="chapter-' . $chapter['id'] . '">' . htmlspecialchars($chapter['title']) . '</div>';
            
            $htmlContent = $Parsedown->text($chapter['content']);
            $html .= '<div class="chapter-content">' . $htmlContent . '</div>';
            $html .= '</div>';
            
            if ($index < count($chapters) - 1) {
                $html .= '<hr style="margin: 30px 0;">';
            }
        }
        
        $html .= '<div class="footer">
            Экспортировано из ' . APP_NAME . ' - ' . date('d.m.Y H:i') . '<br>
            Автор: ' . htmlspecialchars($author_name) . ' | Всего глав: ' . count($chapters) . ' | Всего слов: ' . array_sum(array_column($chapters, 'word_count')) . '
        </div>
    </body>
    </html>';
    
    $filename = cleanFilename($book['title']) . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $html;
    exit;
}

function exportTXT($book, $chapters, $is_public, $author_name) {
    $content = "=" . str_repeat("=", 80) . "=\n";
    $content .= str_pad($book['title'], 80, " ", STR_PAD_BOTH) . "\n";
    $content .= str_pad($author_name, 80, " ", STR_PAD_BOTH) . "\n";
    $content .= "=" . str_repeat("=", 80) . "=\n\n";
    
    if (!empty($book['genre'])) {
        $content .= "Жанр: " . $book['genre'] . "\n\n";
    }
    
    if (!empty($book['description'])) {
        $content .= "ОПИСАНИЕ:\n";
        // Ширина до 144 символов
        $content .= wordwrap($book['description'], 144) . "\n\n";
    }
    
    // Оглавление
    if (!empty($chapters)) {
        $content .= "ОГЛАВЛЕНИЕ:\n";
        $content .= str_repeat("-", 60) . "\n";
        foreach ($chapters as $index => $chapter) {
            $chapter_number = $index + 1;
            $content .= "{$chapter_number}. {$chapter['title']}\n";
        }
        $content .= "\n";
    }
    
    $content .= str_repeat("-", 144) . "\n\n";
    
    foreach ($chapters as $index => $chapter) {
        $content .= $chapter['title'] . "\n";
        $content .= str_repeat("-", 60) . "\n\n";
        
        // Получаем очищенный текст и разбиваем на абзацы
        $cleanContent = cleanMarkdown($chapter['content']);
        $paragraphs = markdownToParagraphs($cleanContent);
        
        foreach ($paragraphs as $paragraph) {
            if (!empty(trim($paragraph))) {
                // Увеличиваем ширину до 144 символов
                $content .= wordwrap($paragraph, 144) . "\n\n";
            }
        }
        
        if ($index < count($chapters) - 1) {
            $content .= str_repeat("-", 144) . "\n\n";
        }
    }
    
    $content .= "\n" . str_repeat("=", 144) . "\n";
    $content .= "Экспортировано из " . APP_NAME . " - " . date('d.m.Y H:i') . "\n";
    $content .= "Автор: " . $author_name . " | Всего глав: " . count($chapters) . " | Всего слов: " . array_sum(array_column($chapters, 'word_count')) . "\n";
    $content .= str_repeat("=", 144) . "\n";
    
    $filename = cleanFilename($book['title']) . '.txt';
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $content;
    exit;
}
?>