<?php
$servername = "localhost";
$username   = "root";   // الافتراضي في XAMPP
$password   = "";       // الافتراضي بدون كلمة مرور
$dbname     = "users_db"; // اسم قاعدة البيانات عندك

// إنشاء الاتصال
$conn = mysqli_connect($servername, $username, $password, $dbname);

// التحقق من الاتصال
if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}
?>
