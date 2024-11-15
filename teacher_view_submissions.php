<?php
include 'db.php';

// Check if user is logged in as a teacher
if (!isset($_SESSION['teacher_id'])) {
    echo "Access denied.";
    exit();
}

$classroomId = $_GET['classroom_id'] ?? null;

if (!$classroomId) {
    echo "Classroom ID is missing.";
    exit();
}

// Fetch submissions grouped by assignment and ordered by submission_date
$stmt = $pdo->prepare("
    SELECT a.assignment_id, a.assignment_description, s.submission_id, s.submission_description, s.submission_date, s.submission_file, st.student_name
    FROM assignment a
    JOIN submission s ON a.assignment_id = s.s_assignment_id
    JOIN student st ON s.s_student_id = st.student_id
    WHERE a.assignment_id IN (
        SELECT p_assignment_id FROM posted_in WHERE p_classroom_id = :classroomId
    )
    ORDER BY a.assignment_id, s.submission_date
");

$stmt->bindParam(':classroomId', $classroomId, PDO::PARAM_INT);
$stmt->execute();
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($submissions):
    $currentAssignmentId = null;
    foreach ($submissions as $submission):
        // Check if we are in a new assignment group
        if ($submission['assignment_id'] !== $currentAssignmentId):
            if ($currentAssignmentId !== null) {
                echo '</ul>';
            }
            $currentAssignmentId = $submission['assignment_id'];
            echo "<h3>Assignment: " . htmlspecialchars($submission['assignment_description']) . "</h3>";
            echo '<ul>';
        endif;
        // Display each submission under the current assignment group
        echo "<li>";
        echo "<p><strong>Submitted by:</strong> " . htmlspecialchars($submission['student_name']) . "</p>";
        echo "<p><strong>Description:</strong> " . htmlspecialchars($submission['submission_description']) . "</p>";
        echo "<p><strong>Date:</strong> " . htmlspecialchars($submission['submission_date']) . "</p>";
        echo '<p><a href="download.php?submission_id=' . urlencode($submission['submission_id']) . '">Download File</a></p>';
        echo "</li>";
    endforeach;
    echo '</ul>';
else:
    echo "<p>No submissions available.</p>";
endif;
?>

<head>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>