<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['teacher_id']) && !isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

// Confirmation page structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="confirm-logout">
        <h2>Are you sure you want to log out?</h2>
        
        <form action="user_logout.php" method="post" style="display:inline;">
            <button type="submit" class="confirm-button">Yes, Log me out</button>
        </form>
        <a href="user_profile.php" class="cancel-button">Cancel</a>
    </div>
</body>
</html>