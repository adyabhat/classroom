<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['teacher_id']) && !isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

$classrooms = [];

if (isset($_SESSION['teacher_id'])) {
    // Fetch classrooms taught by the teacher
    $stmt = $pdo->prepare("SELECT classroom_id, classroom_subject FROM classroom WHERE c_teacher_id = :teacher_id");
    $stmt->bindParam(':teacher_id', $_SESSION['teacher_id'], PDO::PARAM_INT);
    $stmt->execute();
    $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch classrooms the teacher is associated with (via 'teaches')
    $stmtTeaches = $pdo->prepare("
        SELECT t.t_classroom_id AS classroom_id, c.classroom_subject
        FROM teaches t
        JOIN classroom c ON t.t_classroom_id = c.classroom_id
        WHERE t.t_teacher_id = :teacher_id
    ");
    $stmtTeaches->bindParam(':teacher_id', $_SESSION['teacher_id'], PDO::PARAM_INT);
    $stmtTeaches->execute();
    $teachesClassrooms = $stmtTeaches->fetchAll(PDO::FETCH_ASSOC);

    // Merge both arrays if needed
    $classrooms = array_merge($classrooms, $teachesClassrooms);
} else {
    // Fetch classrooms the student has joined
    $stmt = $pdo->prepare(
        "SELECT c.classroom_id, c.classroom_subject 
        FROM has h
        JOIN classroom c ON h.h_classroom_id = c.classroom_id
        WHERE h.h_student_id = :student_id"
    );
    $stmt->bindParam(':student_id', $_SESSION['student_id'], PDO::PARAM_INT);
    $stmt->execute();
    $classrooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Display classrooms or a message if none are found
if (count($classrooms) > 0) {
    // Display classrooms as tiles
    foreach ($classrooms as $classroom) {
        echo '<div class="classroom-tile">';
        echo '<a href="classroom_home.php?classroom_id=' . htmlspecialchars($classroom['classroom_id']) . '">';
        echo '<p><span>' . htmlspecialchars($classroom['classroom_subject']) . '</span></p>';
        echo '</a>';
        echo '</div>';
    }
} else {
    // If no classrooms are found
    echo '<p>You haven\'t joined any classrooms yet. <a href="classroom_join.php">Join a classroom</a></p>';
}
?>