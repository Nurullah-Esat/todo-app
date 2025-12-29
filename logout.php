<?php
session_start();
session_destroy(); // Tüm oturum verilerini yok et
header("Location: login.html"); // Kullanıcıyı giriş sayfasına yönlendir
exit;
?>
