<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // التحقق من المستخدم
    $sql = "SELECT * FROM users WHERE email='$email' AND password=MD5('$password')";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $_SESSION['user'] = $email;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "البريد أو كلمة المرور غير صحيحة";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تسجيل الدخول</title>
  <style>
    body {font-family: Tahoma; background: #f0f0f0; display:flex; justify-content:center; align-items:center; height:100vh;}
    .box {background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 10px #aaa; width:300px;}
    input {width:100%; padding:10px; margin:10px 0; border:1px solid #ccc; border-radius:5px;}
    button {width:100%; padding:10px; background:#3498db; color:#fff; border:none; border-radius:5px; cursor:pointer;}
    button:hover {background:#2980b9;}
    .error {color:red; margin:10px 0;}
  </style>
</head>
<body>
  <div class="box">
    <h2>تسجيل الدخول</h2>
    <form method="POST">
      <input type="email" name="email" placeholder="البريد الإلكتروني" required>
      <input type="password" name="password" placeholder="كلمة المرور" required>
      <button type="submit">دخول</button>
      <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    </form>
  </div>
</body>
</html>
