<?php
session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

header("Location: index.php"); // Redirect to login or homepage
exit();
?>

<link rel="stylesheet" href="styles.css">
<link rel="icon" href="./images/gcc-logo.png" type="image/icon type">