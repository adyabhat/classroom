<?php
session_start();
session_unset();    // Unset all session variables
session_destroy();  // Destroy the session
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if email is present
    $stmt = $pdo->prepare("SELECT teacher_id, teacher_name, teacher_phone, teacher_password FROM teacher WHERE teacher_email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() != 1) {
        echo "Login unsuccessful. Enter the registered email.";
    } else {
        // Fetch the row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stored_password = $row['teacher_password'];
        $teacher_id = $row['teacher_id'];

        if ($password == $stored_password) {
            // Set session variables and redirect to teacher dashboard
            $_SESSION['teacher_id'] = $teacher_id;
            $_SESSION['teacher_email'] = $email;
            $_SESSION['teacher_name'] = $row['teacher_name'];    
            $_SESSION['teacher_phone'] = $row['teacher_phone'];
            
            // Redirect to teacher dashboard page after successful login
            header("Location: teacher_dashboard.php");
            exit();
        } else {
            echo "Login unsuccessful. Enter the correct password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>
<div class="outer-login-box">
<body>
    <div class="login-box">
        <h2>Teacher Login</h2>

        <form method="post" action="teacher_login.php">
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required><br>
            
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</div>
</html>