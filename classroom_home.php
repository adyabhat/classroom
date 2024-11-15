<?php
session_start();
include 'db.php';

// Check if user is logged in and a classroom ID is provided
if (!isset($_SESSION['teacher_id']) && !isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$classroomId = $_GET['classroom_id'] ?? null;
$classroomCode = null;

if ($classroomId) {
    // Fetch classroom details
    $stmt = $pdo->prepare("SELECT classroom_subject, classroom_joining_code FROM classroom WHERE classroom_id = :classroomId");
    $stmt->bindParam(':classroomId', $classroomId, PDO::PARAM_INT);
    $stmt->execute();
    $classroom = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$classroom) {
        echo "Classroom not found.";
        exit();
    }
} else {
    echo "Classroom ID is missing.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($classroom['classroom_subject']); ?> - Classroom</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="classroom-info">
    <h1><?php echo htmlspecialchars($classroom['classroom_subject']); ?></h1>
    <p><strong>Joining Code:</strong> <?php echo htmlspecialchars($classroom['classroom_joining_code']); ?></p>
</div>

<div class="classroom-nav">
    <a href="?classroom_id=<?php echo $classroomId; ?>&view=stream" class="btn">Stream</a>
    <a href="?classroom_id=<?php echo $classroomId; ?>&view=post" class="btn">Posts</a>
    <a href="?classroom_id=<?php echo $classroomId; ?>&view=people" class="btn">People</a>
    <a href="?classroom_id=<?php echo $classroomId; ?>&view=submissions" class="btn">Submissions</a>
</div>

<div class="classroom-content">
    <?php
    $view = $_GET['view'] ?? 'stream';
    switch ($view) {
        case 'stream':
            include 'classroom_stream.php';
            break;
        case 'post':
            include 'classroom_post.php';
            break;
        case 'people':
            include 'classroom_people.php';
            break;
        case 'submissions':
            if (isset($_SESSION['teacher_id'])) {
                include 'teacher_view_submissions.php';
            }
            else {
                include 'student_view_submissions.php';
            }
            break;
        default:
            include 'classroom_stream.php';
            break;
    }
    ?>
</div>


</body>
</html>