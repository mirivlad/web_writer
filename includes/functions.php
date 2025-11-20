<?php
// includes/functions.php

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function transliterate($text) {
    $cyr = [
        'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
        'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
        'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
        'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я',
        ' ',',','!','?',':',';','"','\'','(',')','[',']','{','}'
    ];
    
    $lat = [
        'a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
        'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
        'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
        'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','E','Yu','Ya',
        '_','_','_','_','_','_','_','_','_','_','_','_','_','_'
    ];
    
    return str_replace($cyr, $lat, $text);
}

// Функция для очистки имени файла с транслитерацией
function cleanFilename($filename) {
    // Сначала транслитерируем
    $filename = transliterate($filename);
    
    // Затем убираем оставшиеся недопустимые символы
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
    
    // Убираем множественные подчеркивания
    $filename = preg_replace('/_{2,}/', '_', $filename);
    
    // Убираем подчеркивания с начала и конца
    $filename = trim($filename, '_');
    
    // Если после очистки имя файла пустое, используем стандартное
    if (empty($filename)) {
        $filename = 'book';
    }
    
    // Ограничиваем длину имени файла
    if (strlen($filename) > 100) {
        $filename = substr($filename, 0, 100);
    }
    
    return $filename;
}
?>