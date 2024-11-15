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
    $stmt = $pdo->prepare("SELECT student_id, student_name, student_phone, student_password FROM student WHERE student_email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() != 1) {
        echo "Login unsuccessful. Enter the registered email.";
    } else {
        // Fetch the row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stored_password = $row['student_password'];
        $student_id = $row['student_id'];

        if ($password == $stored_password) {
            // Set session variables and redirect to student dashboard
            $_SESSION['student_id'] = $student_id;
            $_SESSION['student_email'] = $email;
            $_SESSION['student_name'] = $row['student_name'];
            $_SESSION['student_phone'] = $row['student_phone'];
            // Redirect to student dashboard page after successful login
            header("Location: student_dashboard.php");
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
        <h2>Student Login</h2>

        <form method="post" action="student_login.php">
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