<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db.php'; // Veritabanı bağlantısını dahil et
session_start();

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Kullanıcı giriş yapmamış.']);
    exit;
}

$user_id = $_SESSION['user_id']; // Giriş yapan kullanıcının ID'si

try {
    $action = $_GET['action'] ?? ''; // İstek türünü belirle

    if ($action === 'fetch') {
        // Görevleri getir
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tasks);
        exit;
    } elseif ($action === 'add') {
        // Yeni görev ekle
        $text = $_POST['text'] ?? null;
        if (!$text) {
            echo json_encode(['error' => 'Görev metni boş olamaz']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO tasks (text, user_id, created_at) VALUES (:text, :user_id, NOW())");
        $stmt->execute(['text' => $text, 'user_id' => $user_id]);
        echo json_encode(['id' => $pdo->lastInsertId(), 'text' => $text, 'done' => false]);
        exit;
    } elseif ($action === 'update') {
        // Görevi güncelle (tamamla veya geri al)
        $id = $_POST['id'] ?? null;
        $done = isset($_POST['done']) ? (int)$_POST['done'] : null;

        if (!$id || $done === null) {
            echo json_encode(['error' => 'Geçersiz veri']);
            exit;
        }

        // Görevi güncelle ve tamamlanma süresini hesapla
        $completed_at = $done ? 'NOW()' : 'NULL';
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET done = :done, completed_at = $completed_at, 
                completion_time = IF(:done, TIMESTAMPDIFF(SECOND, created_at, NOW()), NULL) 
            WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute(['done' => $done, 'id' => $id, 'user_id' => $user_id]);
        echo json_encode(['status' => 'success']);
        exit;
    } elseif ($action === 'edit') {
        // Görev metnini düzenle
        $id = $_POST['id'] ?? null;
        $text = $_POST['text'] ?? null;

        if (!$id || !$text) {
            echo json_encode(['error' => 'Geçersiz veri']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE tasks SET text = :text WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['text' => $text, 'id' => $id, 'user_id' => $user_id]);

        echo json_encode(['status' => 'success', 'text' => $text]);
        exit;
    } elseif ($action === 'delete') {
        // Görevi sil
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['error' => 'Geçersiz görev ID']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $user_id]);
        echo json_encode(['status' => 'success']);
        exit;
    } elseif ($action === 'deleteAll') {
        // Tüm görevleri sil
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        echo json_encode(['status' => 'success']);
        exit;
    } elseif ($action === 'deleteCompleted') {
        // Tamamlanmış görevleri sil
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE done = 1 AND user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        echo json_encode(['status' => 'success']);
        exit;
    } else {
        echo json_encode(['error' => 'Geçersiz işlem']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Bir hata oluştu: ' . $e->getMessage()]);
    exit;
}
?>
