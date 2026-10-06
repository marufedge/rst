<?php
// Database configuration
define('DB_HOST', 'sql102.ezyro.com');
define('DB_USER', 'ezyro_41262913');
define('DB_PASS', 'marufmylife');
define('DB_NAME', 'ezyro_41262913_sales_erp'); // Fixed: Added the correct database name format

// Application configuration
define('APP_NAME', 'Sales & Delivery ERP');
define('APP_URL', 'http://yarmook.unaux.com/sales-erp');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Create upload directories if they don't exist
$upload_dirs = ['sales', 'delivery', 'deposits', 'reports'];
foreach ($upload_dirs as $dir) {
    if (!file_exists(UPLOAD_PATH . $dir)) {
        mkdir(UPLOAD_PATH . $dir, 0777, true);
    }
}

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Function to upload file
function uploadFile($file, $folder) {
    $target_dir = UPLOAD_PATH . $folder . '/';
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'message' => 'Upload failed'];
    }
}
?>