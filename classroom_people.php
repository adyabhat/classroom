<?php
$classroomId = $_GET['classroom_id'];

// Get the creator
$stmt = $pdo->prepare(
    "SELECT teacher_name AS name 
    FROM teacher 
    WHERE teacher_id = (SELECT c_teacher_id 
                        FROM classroom 
                        WHERE classroom_id = :classroomId)");
$stmt->bindParam(':classroomId', $classroomId, PDO::PARAM_INT);
$stmt->execute();
$creator = $stmt->fetch(PDO::FETCH_ASSOC);

echo '<section class="classroom-creator">';
echo '<h3>Classroom Creator</h3>';
echo '<p>' . htmlspecialchars($creator['name']) . '</p>';
echo '</section>';

// Get other teachers
$stmt = $pdo->prepare(
    "SELECT t.teacher_name AS name 
    FROM teaches ts
    JOIN teacher t ON t.teacher_id = ts.t_teacher_id 
    WHERE ts.t_classroom_id = :classroomId");
$stmt->bindParam(':classroomId', $classroomId, PDO::PARAM_INT);
$stmt->execute();
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($teachers) {
    echo '<section class="teachers">';
    echo '<h3>Teachers</h3>';
    foreach ($teachers as $teacher) {
        echo '<p>' . htmlspecialchars($teacher['name']) . '</p>';
    }
    echo '</section>';
}

// Get students count using get_student_count_in_classroom function
$stmt = $pdo->prepare(
    "SELECT get_student_count_in_classroom(:classroomId) AS student_count"
);
$stmt->bindParam(':classroomId', $classroomId, PDO::PARAM_INT);
$stmt->execute();
$studentCount = $stmt->fetch(PDO::FETCH_ASSOC)['student_count'];

// Display the student count title
echo "<section class='students'>";
echo "<h3>Students <span class='student-count'>($studentCount)</span></h3>";

// Get student names
$stmt = $pdo->prepare(
    "SELECT s.student_name AS name 
    FROM student s
    JOIN has h ON s.student_id = h.h_student_id 
    WHERE h.h_classroom_id = :classroomId");
$stmt->bindParam(':classroomId', $classroomId, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($students) {
    foreach ($students as $student) {
        echo '<p>' . htmlspecialchars($student['name']) . '</p>';
    }
}
echo '</section>';
?>

<head>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>