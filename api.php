<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'key.php';
    if (isset($_POST['key']) && $_POST['key'] == $apiKey) {
        $gender = $_POST['gender'];
        $watched_at = isset($_POST['watched_at']) && !empty($_POST['watched_at']) ? $_POST['watched_at'] : date('Y-m-d H:i:s');
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
