<?php
include 'db.php';

// Check if the user is logged in as a student
if (!isset($_SESSION['student_id'])) {
    echo "Access denied.";
    exit();
}

$studentId = $_SESSION['student_id'];
$classroomId = $_GET['classroom_id'] ?? null;

if (!$classroomId) {
    echo "Classroom ID is missing.";
    exit();
}

// Fetch assignments and the student's submissions ordered by assignment due date
$stmt = $pdo->prepare("
    SELECT a.assignment_id, a.assignment_description, a.assignment_due_date,
           s.submission_id, s.submission_description, s.submission_date, s.submission_file
    FROM assignment a
    LEFT JOIN submission s ON a.assignment_id = s.s_assignment_id AND s.s_student_id = :studentId
    WHERE a.assignment_id IN (
        SELECT p_assignment_id FROM posted_in WHERE p_classroom_id = :classroomId
    )
    ORDER BY a.assignment_due_date
");

$stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
$stmt->bindParam(':classroomId', $classroomId, PDO::PARAM_INT);
$stmt->execute();
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Submissions</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>
<body>
    <h3>My Submissions</h3>

    <?php if ($assignments): ?>
        <ul>
            <?php foreach ($assignments as $assignment): ?>
                <li>
                    <h3>Assignment: <?php echo htmlspecialchars($assignment['assignment_description']); ?></h3>
                    <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment['assignment_due_date']); ?></p>

                    <?php if ($assignment['submission_id']): ?>
                        <p><strong>Submission Description:</strong> <?php echo htmlspecialchars($assignment['submission_description']); ?></p>
                        <p><strong>Submission Date:</strong> <?php echo htmlspecialchars($assignment['submission_date']); ?></p>
                        <p><a href="download.php?submission_id=<?php echo urlencode($assignment['submission_id']); ?>">Download Your Submission</a></p>
                    <?php else: ?>
                        <p><em>No submission made yet for this assignment.</em></p>
                    <?php endif; ?>
                </li>
                <hr>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No assignments available in this classroom.</p>
    <?php endif; ?>
</body>
</html>
