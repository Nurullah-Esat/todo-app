<?php
// Veritabanı bağlantısı
$servername = "localhost";
$username = "root"; // Veritabanı kullanıcı adınız
$password = ""; // Veritabanı şifreniz
$dbname = "todo_app"; // Veritabanı adı

$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı kontrolü
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Kullanıcı kimliği (Bu, oturum açmış kullanıcıdan alınmalıdır)
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Oturum açılmadı"]);
    exit;
}
$user_id = $_SESSION['user_id'];

// Gelen isteği kontrol et
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'fetch') {
    // Kategorileri getir
    $sql = "SELECT id, name FROM categories WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    echo json_encode($categories);

} elseif ($action === 'add') {
    // Yeni kategori ekle
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';

    if ($name === '') {
        echo json_encode(["error" => "Kategori adı boş olamaz!"]);
        exit;
    }

    $sql = "INSERT INTO categories (name, user_id, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $user_id);

    if ($stmt->execute()) {
        $newCategory = [
            "id" => $stmt->insert_id,
            "name" => $name
        ];
        echo json_encode($newCategory);
    } else {
        echo json_encode(["error" => "Kategori eklenemedi!"]);
    }

} elseif ($action === 'delete') {
    // Kategoriyi sil
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(["error" => "Geçersiz kategori ID!"]);
        exit;
    }

    $sql = "DELETE FROM categories WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["error" => "Kategori silinemedi!"]);
    }

} else {
    // Geçersiz işlem
    echo json_encode(["error" => "Geçersiz işlem"]);
}

$conn->close();
