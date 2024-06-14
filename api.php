<?php
date_default_timezone_set('Asia/Riyadh');

require 'db.php';
$requestMethod = $_SERVER['REQUEST_METHOD'];
$key = $requestMethod === 'POST' ? $_POST['key'] ?? null : $_GET['key'] ?? null;

if ($requestMethod === 'POST' || $requestMethod === 'GET') {
    require 'key.php';
    if (isset($key) && $key == $apiKey) {
        $gender = $requestMethod === 'POST' ? $_POST['gender'] ?? null : $_GET['gender'] ?? null;
        $watched_at = $requestMethod === 'POST' ? ($_POST['watched_at'] ?? date('Y-m-d H:i:s')) : ($_GET['watched_at'] ?? date('Y-m-d H:i:s'));

        if (in_array($gender, ['male', 'female', 'family'])) {
            $stmt = $db->prepare("INSERT INTO ad_watches (gender, watched_at) VALUES (:gender, :watched_at)");
            $stmt->bindValue(':gender', $gender, SQLITE3_TEXT);
            $stmt->bindValue(':watched_at', $watched_at, SQLITE3_TEXT);
            $stmt->execute();
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid gender']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid key']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
