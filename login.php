<?php
session_start();
include 'db.php'; // ملف الاتصال بقاعدة البيانات

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // التأكد أن الحقول مش فاضية
    if (empty($email) || empty($password)) {
        echo "يرجى إدخال البريد الإلكتروني وكلمة المرور";
        exit();
    }

    // البحث عن المستخدم
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // التحقق من كلمة السر
if ($password === $user['password']) {


          $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];


            // توجيه حسب الدور
            if ($user['role'] === "admin") {
                header("Location: dashboard.php");
            } else {
                header("Location: index.html"); 
            }
            exit();
        } else {
            echo "❌ كلمة المرور غير صحيحة";
        }
    } else {
        echo "❌ البريد الإلكتروني غير موجود";
    }
}
?>
