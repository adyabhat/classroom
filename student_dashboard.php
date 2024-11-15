<?php
include 'header.php';
?>
<link rel="stylesheet" href="styles.css">
<link rel="icon" href="./images/gcc-logo.png" type="image/icon type">

<h1>Welcome to the Student Dashboard, <?php echo $_SESSION['student_name'] ?></h1>

<div class="classrooms-container">
    <?php include 'classroom_button.php'; ?>
</div>