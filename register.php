<?php
include 'db.php'; // الاتصال بقاعدة البيانات

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $hashed_password = $password;

    // التأكد من عدم وجود نفس الإيميل قبل كده
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('هذا البريد مسجل بالفعل'); window.location.href='register.html';</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            echo "<script>alert('تم التسجيل بنجاح! يمكنك تسجيل الدخول الآن'); window.location.href='login.html';</script>";
        } else {
            echo "خطأ: " . $stmt->error;
        }
    }
}
?>
