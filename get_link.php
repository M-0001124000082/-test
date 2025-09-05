<?php
$servername = "localhost";
$username = "root"; // عدل حسب بياناتك
$password = "";
$dbname = "platform"; // اسم قاعدة البيانات

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$sql = "SELECT grade, url FROM grades_links";
$result = $conn->query($sql);

$links = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $links[$row['grade']] = $row['url'];
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($links);

$conn->close();
?>
