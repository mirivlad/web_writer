<?php
// views/chapters/preview.php
include 'views/layouts/header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/pico.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
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
        h1 { border-bottom: 2px solid var(--primary); padding-bottom: 0.3em; }
        h2 { border-bottom: 1px solid var(--border-color); padding-bottom: 0.3em; }
        p {
            margin-bottom: 1em;
        }
        code {
            background: var(--card-background-color);
            padding: 2px 4px;
            border-radius: 3px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        }
        pre {
            background: var(--card-background-color);
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 4px solid var(--primary);
        }
        pre code {
            background: none;
            padding: 0;
        }
        blockquote {
            border-left: 4px solid var(--border-color);
            padding-left: 1rem;
            margin-left: 0;
            color: var(--muted-color);
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
            border: 1px solid var(--border-color);
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background: var(--card-background-color);
        }
    </style>
</head>
<body>
    <header>
        <h1><?= e($title) ?></h1>
        <hr>
    </header>
    
    <main class="content">
        <?= $content ?>
    </main>
    
    <footer style="margin-top: 3rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
        <small>Сгенерировано <?= date('d.m.Y H:i') ?> | Markdown Preview</small>
        <br>
        <a href="javascript:window.close()" class="button secondary">Закрыть</a>
        <a href="javascript:window.print()" class="button">Печать</a>
    </footer>
</body>
</html>