<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    // Check if email is unique
    $email_check = $pdo->prepare("SELECT student_id FROM student WHERE student_email = :email");
    $email_check->bindParam(":email", $email);
    $email_check->execute();

    if ($email_check->rowCount() > 0) {
        echo "Email already exists. Please use a different email.";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO student (student_name, student_email, student_phone, student_password) 
            VALUES (:name, :email, :phone, :password)");
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":password", $password);

        if ($stmt->execute()) {
            // Redirect to student login page after successful signup
            header("Location: student_login.php");
            exit();
        } else {
            echo "Error: " . $stmt->errorInfo()[2];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up as Student</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>
<div class="outer-login-box">
<body>
    <div class="login-box">
        <h2>Sign Up as a Student</h2>

        <form method="post" action="student_sign_up.php">
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required><br> <!--for of label and id of input must be same-->
            
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required><br>
            
            <label for="phone">Phone:</label><br>
            <input type="phone" id="phone" name="phone" pattern="[6-9]{1}[0-9]{9}" required><br>

            <label for="password">Set Password:</label><br>
            <input type="password" id="password" name="password" required><br>

            <button type="submit">Sign Up as Student</button>
        </form>
    </div>
</body>
</div>
</html>