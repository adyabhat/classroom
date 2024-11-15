<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['teacher_id']) && !isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$dashboard_link = isset($_SESSION['teacher_id']) ? 'teacher_dashboard.php' : 'student_dashboard.php';
$join_classroom_link = 'classroom_join.php';
$profile_link = 'user_profile.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href=".\css\styles.css"> -->
    <title>Dashboard</title>
    <link rel="icon" href=".\images\gcc-logo.png" type="image/icon type">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header-header">
        <header>
            <nav>
                <div class="nav-left">
                    <a href="<?php echo $dashboard_link; ?>">Dashboard</a>
                    <a href="<?php echo $join_classroom_link; ?>"> 
                        <?php 
                            if (isset($_SESSION['teacher_id'])) {
                                echo "Join/ Create Classroom ";
                            }

                            else {
                                echo "Join Classroom ";
                            }
                        ?> 
                    </a>
                </div>
                <div class="nav-right">
                    <a href="<?php echo $profile_link; ?>">Profile</a>
                </div>
            </nav>
        </header>
    </div>
</body>
</html>