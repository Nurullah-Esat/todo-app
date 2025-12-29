<?php
$host = "localhost";
$dbname = "todo_app"; // Veritabanı adı
$username = "root";   // XAMPP'ın varsayılan kullanıcı adı
$password = "";       // XAMPP'ın varsayılan şifresi (boş)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo ifadesi kaldırıldı, artık JSON çıktısını bozmaz
} catch (PDOException $e) {
    die(json_encode(["error" => "Veritabanı bağlantısı başarısız: " . $e->getMessage()]));
}
?>
