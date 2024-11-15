<?php
include 'header.php';
?>
<link rel="stylesheet" href="styles.css">
<link rel="icon" href="./images/gcc-logo.png" type="image/icon type">

<h1 class="dashboard-h1">Welcome to the Teacher Dashboard, <?php echo $_SESSION['teacher_name']?> </h1>

<div class="classrooms-container">
    <?php include 'classroom_button.php'; ?>
</div>