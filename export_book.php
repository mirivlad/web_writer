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

// Функция для очистки имени файла
// function cleanFilename($filename) {
//     return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
// }

// Функция для преобразования Markdown в чистый текст с форматированием абзацев
function markdownToPlainText($markdown) {
    // Обрабатываем диалоги (заменяем - на —)
    $markdown = preg_replace('/^- (.+)$/m', "— $1", $markdown);
    
    // Убираем Markdown разметку
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
    
    // Обрабатываем списки
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
        exportPDF($book, $chapters, $is_public);
        break;
    case 'docx':
        exportDOCX($book, $chapters, $is_public);
        break;
    case 'odt':
        exportODT($book, $chapters, $is_public);
        break;
    case 'html':
        exportHTML($book, $chapters, $is_public);
        break;
    case 'txt':
        exportTXT($book, $chapters, $is_public);
        break;
    default:
        $_SESSION['error'] = "Неверный формат экспорта";
        redirect($share_token ? "view_book.php?share_token=$share_token" : "book_edit.php?id=$book_id");
}

function exportPDF($book, $chapters, $is_public) {
    global $Parsedown;
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Устанавливаем метаданные документа
    $pdf->SetCreator(APP_NAME);
    $pdf->SetAuthor($is_public ? 'Автор' : ($_SESSION['display_name'] ?? 'Автор'));
    $pdf->SetTitle($book['title']);
    $pdf->SetSubject($book['genre'] ?? '');
    
    // Устанавливаем margins
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);
    
    // Устанавливаем авто разрыв страниц
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Добавляем страницу
    $pdf->AddPage();
    
    // Устанавливаем шрифт с поддержкой кириллицы
    $pdf->SetFont('dejavusans', '', 12);
    
    // Заголовок книги
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, $book['title'], 0, 1, 'C');
    $pdf->Ln(5);
    
    // Жанр
    if (!empty($book['genre'])) {
        $pdf->SetFont('dejavusans', 'I', 12);
        $pdf->Cell(0, 10, 'Жанр: ' . $book['genre'], 0, 1, 'C');
        $pdf->Ln(5);
    }
    
    // Описание
    if (!empty($book['description'])) {
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->MultiCell(0, 8, $book['description'], 0, 'J');
        $pdf->Ln(10);
    }
    
    // Разделитель
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(10);
    
    // Главы
    foreach ($chapters as $index => $chapter) {
        $pdf->SetFont('dejavusans', '', 12);
        
        // Название главы
        $pdf->SetFont('dejavusans', 'B', 14);
        $pdf->Cell(0, 8, $chapter['title'], 0, 1);
        $pdf->Ln(2);
        
        // Контент главы (форматированный HTML)
        $pdf->SetFont('dejavusans', '', 11);
        $htmlContent = $Parsedown->text($chapter['content']);
        $pdf->writeHTML($htmlContent, true, false, true, false, '');
        
        $pdf->Ln(8);
        
        // Разделитель между главами (кроме последней)
        if ($index < count($chapters) - 1) {
            $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->Ln(8);
        }
    }
    
    // Футер с информацией
    $pdf->SetY(-25);
    $pdf->SetFont('dejavusans', 'I', 8);
    $pdf->Cell(0, 6, 'Экспортировано из ' . APP_NAME . ' - ' . date('d.m.Y H:i'), 0, 1, 'C');
    $pdf->Cell(0, 6, 'Всего глав: ' . count($chapters) . ' | Всего слов: ' . array_sum(array_column($chapters, 'word_count')), 0, 1, 'C');
    
    // Отправляем файл
    $filename = cleanFilename($book['title']) . '.pdf';
    $pdf->Output($filename, 'D');
    exit;
}

function exportDOCX($book, $chapters, $is_public) {
    global $Parsedown;
    
    $phpWord = new PhpWord();
    
    // Стили документа
    $phpWord->setDefaultFontName('Times New Roman');
    $phpWord->setDefaultFontSize(12);
    
    // Секция документа
    $section = $phpWord->addSection();
    
    // Заголовок книги
    $section->addText($book['title'], ['bold' => true, 'size' => 16], ['alignment' => 'center']);
    $section->addTextBreak(2);
    
    // Жанр
    if (!empty($book['genre'])) {
        $section->addText('Жанр: ' . $book['genre'], ['italic' => true], ['alignment' => 'center']);
        $section->addTextBreak(1);
    }
    
    // Описание
    if (!empty($book['description'])) {
        $section->addText($book['description']);
        $section->addTextBreak(2);
    }
    
    // Разделитель
    $section->addText('СОДЕРЖАНИЕ', ['bold' => true, 'size' => 14], ['alignment' => 'center']);
    $section->addTextBreak(2);
    
    // Главы
    foreach ($chapters as $index => $chapter) {
        // Заголовок главы
        $section->addText($chapter['title'], ['bold' => true, 'size' => 14]);
        
        // Контент главы (форматированный HTML)
        $htmlContent = $Parsedown->text($chapter['content']);
        
        // Упрощенное добавление HTML контента
        $plainContent = strip_tags($htmlContent);
        $paragraphs = explode("\n\n", $plainContent);
        
        foreach ($paragraphs as $paragraph) {
            if (trim($paragraph)) {
                $section->addText($paragraph);
            }
        }
        
        // Разрыв страницы между главами (кроме последней)
        if ($index < count($chapters) - 1) {
            $section->addPageBreak();
        }
    }
    
    // Футер
    $section->addTextBreak(2);
    $section->addText('Экспортировано из ' . APP_NAME . ' - ' . date('d.m.Y H:i'), ['italic' => true, 'size' => 9]);
    $section->addText('Всего глав: ' . count($chapters) . ' | Всего слов: ' . array_sum(array_column($chapters, 'word_count')), ['italic' => true, 'size' => 9]);
    
    // Сохраняем и отправляем
    $filename = cleanFilename($book['title']) . '.docx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('php://output');
    exit;
}

function exportODT($book, $chapters, $is_public) {
    global $Parsedown;
    
    $phpWord = new PhpWord();
    
    // Стили документа
    $phpWord->setDefaultFontName('Liberation Serif');
    $phpWord->setDefaultFontSize(12);
    
    // Секция документа
    $section = $phpWord->addSection();
    
    // Заголовок книги
    $section->addText($book['title'], ['bold' => true, 'size' => 16], ['alignment' => 'center']);
    $section->addTextBreak(2);
    
    // Жанр
    if (!empty($book['genre'])) {
        $section->addText('Жанр: ' . $book['genre'], ['italic' => true], ['alignment' => 'center']);
        $section->addTextBreak(1);
    }
    
    // Описание
    if (!empty($book['description'])) {
        $section->addText($book['description']);
        $section->addTextBreak(2);
    }
    
    // Главы
    foreach ($chapters as $index => $chapter) {
        // Заголовок главы
        $section->addText($chapter['title'], ['bold' => true, 'size' => 14]);
        
        // Контент главы (форматированный HTML)
        $htmlContent = $Parsedown->text($chapter['content']);
        $plainContent = strip_tags($htmlContent);
        $paragraphs = explode("\n\n", $plainContent);
        
        foreach ($paragraphs as $paragraph) {
            if (trim($paragraph)) {
                $section->addText($paragraph);
            }
        }
        
        $section->addTextBreak(2);
    }
    
    // Футер
    $section->addTextBreak(2);
    $section->addText('Экспортировано из ' . APP_NAME . ' - ' . date('d.m.Y H:i'), ['italic' => true, 'size' => 9]);
    $section->addText('Всего глав: ' . count($chapters) . ' | Всего слов: ' . array_sum(array_column($chapters, 'word_count')), ['italic' => true, 'size' => 9]);
    
    // Сохраняем и отправляем
    $filename = cleanFilename($book['title']) . '.odt';
    header('Content-Type: application/vnd.oasis.opendocument.text');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $objWriter = IOFactory::createWriter($phpWord, 'ODText');
    $objWriter->save('php://output');
    exit;
}

function exportHTML($book, $chapters, $is_public) {
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
                max-width: 800px;
                margin-left: auto;
                margin-right: auto;
                color: #333;
            }
            .book-title { 
                text-align: center; 
                font-size: 24px; 
                font-weight: bold;
                margin-bottom: 10px;
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
            .chapter-title {
                border-bottom: 2px solid #007bff;
                padding-bottom: 10px;
                margin-top: 30px;
                font-size: 20px;
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
            .chapter-content h1, .chapter-content h2, .chapter-content h3 {
                margin-top: 1.5em;
                margin-bottom: 0.5em;
            }
            .chapter-content p {
                margin-bottom: 1em;
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
            .dialogue {
                margin-left: 2rem;
                font-style: italic;
                color: #2c5aa0;
            }
        </style>
    </head>
    <body>
        <div class="book-title">' . htmlspecialchars($book['title']) . '</div>';
        
        if (!empty($book['genre'])) {
            $html .= '<div class="book-genre">Жанр: ' . htmlspecialchars($book['genre']) . '</div>';
        }
        
        if (!empty($book['description'])) {
            $html .= '<div class="book-description">' . nl2br(htmlspecialchars($book['description'])) . '</div>';
        }
        
        $html .= '<hr style="margin: 30px 0;">';
        
        foreach ($chapters as $index => $chapter) {
            $html .= '<div class="chapter">';
            $html .= '<div class="chapter-title">' . htmlspecialchars($chapter['title']) . '</div>';
            
            $htmlContent = $Parsedown->text($chapter['content']);
            $html .= '<div class="chapter-content">' . $htmlContent . '</div>';
            $html .= '</div>';
            
            if ($index < count($chapters) - 1) {
                $html .= '<hr style="margin: 30px 0;">';
            }
        }
        
        $html .= '<div class="footer">
            Экспортировано из ' . APP_NAME . ' - ' . date('d.m.Y H:i') . '<br>
            Всего глав: ' . count($chapters) . ' | Всего слов: ' . array_sum(array_column($chapters, 'word_count')) . '
        </div>
    </body>
    </html>';
    
    $filename = cleanFilename($book['title']) . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $html;
    exit;
}

function exportTXT($book, $chapters, $is_public) {
    $content = "=" . str_repeat("=", 60) . "=\n";
    $content .= str_pad($book['title'], 60, " ", STR_PAD_BOTH) . "\n";
    $content .= "=" . str_repeat("=", 60) . "=\n\n";
    
    if (!empty($book['genre'])) {
        $content .= "Жанр: " . $book['genre'] . "\n\n";
    }
    
    if (!empty($book['description'])) {
        $content .= "ОПИСАНИЕ:\n";
        $content .= wordwrap($book['description'], 80) . "\n\n";
    }
    
    $content .= str_repeat("-", 80) . "\n\n";
    
    foreach ($chapters as $index => $chapter) {
        $content .= $chapter['title'] . "\n";
        $content .= str_repeat("-", 40) . "\n\n";
        
        // Форматируем текст для TXT
        $plainText = markdownToPlainText($chapter['content']);
        $formattedText = formatPlainText($plainText);
        $content .= wordwrap($formattedText, 80) . "\n\n";
        
        if ($index < count($chapters) - 1) {
            $content .= str_repeat("-", 80) . "\n\n";
        }
    }
    
    $content .= "\n" . str_repeat("=", 80) . "\n";
    $content .= "Экспортировано из " . APP_NAME . " - " . date('d.m.Y H:i') . "\n";
    $content .= "Всего глав: " . count($chapters) . " | Всего слов: " . array_sum(array_column($chapters, 'word_count')) . "\n";
    $content .= str_repeat("=", 80) . "\n";
    
    $filename = cleanFilename($book['title']) . '.txt';
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $content;
    exit;
}
?>