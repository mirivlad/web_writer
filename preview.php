<?php
require_once 'config/config.php';
require_login();

require_once 'includes/parsedown/ParsedownExtra.php';
$Parsedown = new ParsedownExtra();;

$content = $_POST['content'] ?? '';
$title = $_POST['title'] ?? 'Предпросмотр';

$Parsedown = new Parsedown();
$html_content = $Parsedown->text($content);

$page_title = "Предпросмотр: " . e($title);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1.5.10/css/pico.min.css">
    <style>
        body {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .content {
            margin-top: 2rem;
        }
        h1, h2, h3, h4, h5, h6 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        h1 { border-bottom: 2px solid #007bff; padding-bottom: 0.3em; }
        h2 { border-bottom: 1px solid #eaecef; padding-bottom: 0.3em; }
        p {
            margin-bottom: 1em;
        }
        code {
            background: #f5f5f5;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        }
        pre {
            background: #f5f5f5;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 4px solid #007bff;
        }
        pre code {
            background: none;
            padding: 0;
        }
        blockquote {
            border-left: 4px solid #ddd;
            padding-left: 1rem;
            margin-left: 0;
            color: #666;
            font-style: italic;
        }
        strong { font-weight: bold; }
        em { font-style: italic; }
        u { text-decoration: underline; }
        del { text-decoration: line-through; }
        .dialogue {
            margin-left: 2rem;
            font-style: italic;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 1rem 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <header>
        <h1><?= e($title) ?></h1>
        <hr>
    </header>
    
    <main class="content">
        <?= $html_content ?>
    </main>
    
    <footer style="margin-top: 3rem; padding-top: 1rem; border-top: 1px solid #ddd;">
        <small>Сгенерировано <?= date('d.m.Y H:i') ?> | Markdown Preview</small>
    </footer>
</body>
</html>