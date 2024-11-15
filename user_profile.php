<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['teacher_id']) && !isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

// Sample profile information
$userId = $_SESSION['teacher_id'] ?? $_SESSION['student_id'] ?? '';
$userName = $_SESSION['teacher_name'] ?? $_SESSION['student_name'] ?? '';
$userEmail = $_SESSION['teacher_email'] ?? $_SESSION['student_email'] ?? '';
$userPhone = $_SESSION['teacher_phone'] ?? $_SESSION['student_phone'] ?? '';


// Profile page structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="profile">
        <h1>Your Profile</h1>
        <p><strong>Website Id:</strong> <?php echo htmlspecialchars($userId); ?></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($userPhone); ?></p>
        
        <a href="user_logout_confirm.php" class="logout-button">Logout</a>
    </div>
</body>
</html>