<?php
$servername = "localhost";
$username   = "root";   // الافتراضي في XAMPP
$password   = "";       // اتركه فاضي لو ما حطيتش باسورد للـ MySQL
$dbname     = "users_db"; // اسم قاعدة البيانات اللي أنشأتها

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
?>
