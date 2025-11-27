<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
            line-height: 1.6;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .preview-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chapter-header {
            border-bottom: 3px solid var(--bs-primary);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .chapter-content {
            font-size: 1.1em;
        }
        .chapter-content h1, .chapter-content h2, .chapter-content h3 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        .chapter-content h1 { 
            border-bottom: 2px solid var(--bs-primary); 
            padding-bottom: 0.3em; 
        }
        .chapter-content h2 { 
            border-bottom: 1px solid var(--bs-border-color); 
            padding-bottom: 0.3em; 
        }
        .chapter-content p {
            margin-bottom: 1em;
            text-align: justify;
        }
        .chapter-content code {
            background: var(--bs-light);
            padding: 2px 4px;
            border-radius: 3px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        }
        .chapter-content pre {
            background: var(--bs-dark);
            color: var(--bs-light);
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 4px solid var(--bs-primary);
        }
        .chapter-content pre code {
            background: none;
            padding: 0;
            display: block;
            color: inherit;
        }
        .chapter-content blockquote {
            border-left: 4px solid var(--bs-border-color);
            padding-left: 1rem;
            margin-left: 0;
            color: var(--bs-secondary);
            font-style: italic;
            background: var(--bs-light);
            padding: 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
        }
        .chapter-content strong { font-weight: bold; }
        .chapter-content em { font-style: italic; }
        .chapter-content u { text-decoration: underline; }
        .chapter-content del { text-decoration: line-through; }
        .chapter-content .dialogue {
            margin-left: 2rem;
            font-style: italic;
            color: #2c5aa0;
        }
        .chapter-content table {
            border-collapse: collapse;
            width: 100%;
            margin: 1rem 0;
        }
        .chapter-content table, .chapter-content th, .chapter-content td {
            border: 1px solid var(--bs-border-color);
        }
        .chapter-content th, .chapter-content td {
            padding: 8px 12px;
            text-align: left;
        }
        .chapter-content th {
            background: var(--bs-light);
        }
        .chapter-content ul, .chapter-content ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        .chapter-content li {
            margin-bottom: 0.3rem;
        }
        .chapter-content img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <header class="chapter-header text-center">
            <h1 class="display-6"><?= e($title) ?></h1>
        </header>
        
        <main class="chapter-content">
            <?= $content ?>
        </main>
        
        <footer class="mt-5 pt-4 border-top text-center">
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Печать
                </button>
                <button onclick="window.close()" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Закрыть
                </button>
            </div>
            <p class="text-muted mt-3 small">
                Сгенерировано <?= date('d.m.Y H:i') ?> | Предпросмотр
            </p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>