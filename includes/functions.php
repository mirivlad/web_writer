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

function handleCoverUpload($file, $book_id) {
    global $pdo;
    
    // Проверяем папку для загрузок
    if (!file_exists(COVERS_PATH)) {
        mkdir(COVERS_PATH, 0755, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Проверка типа файла
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'error' => 'Разрешены только JPG, PNG, GIF и WebP изображения'];
    }
    
    // Проверка размера
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Размер изображения не должен превышать 5MB'];
    }
    
    // Проверка на ошибки загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Ошибка загрузки файла: ' . $file['error']];
    }
    
    // Генерация уникального имени файла
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'cover_' . $book_id . '_' . time() . '.' . $extension;
    $file_path = COVERS_PATH . $filename;
    
    // Удаляем старую обложку если есть
    $bookModel = new Book($pdo);
    $bookModel->deleteCover($book_id);
    
    // Сохраняем новую обложку
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Оптимизируем изображение
        optimizeImage($file_path);
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Не удалось сохранить файл'];
    }
}

function optimizeImage($file_path) {
    // Простая оптимизация - ресайз до максимальных размеров
    list($width, $height, $type) = getimagesize($file_path);
    
    $max_width = 800;
    $max_height = 1200;
    
    if ($width > $max_width || $height > $max_height) {
        // Вычисляем новые размеры
        $ratio = $width / $height;
        if ($max_width / $max_height > $ratio) {
            $new_width = $max_height * $ratio;
            $new_height = $max_height;
        } else {
            $new_width = $max_width;
            $new_height = $max_width / $ratio;
        }
        
        // Создаем новое изображение
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Загружаем исходное изображение в зависимости от типа
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($file_path);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($file_path);
                // Сохраняем прозрачность для PNG
                imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($file_path);
                break;
            default:
                return; // Не поддерживаемый тип
        }
        
        // Ресайз и сохраняем
        imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($new_image, $file_path, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($new_image, $file_path, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($new_image, $file_path);
                break;
        }
        
        // Освобождаем память
        imagedestroy($source);
        imagedestroy($new_image);
    }
}
?>