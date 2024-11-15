<?php
@session_start();
include 'db.php';

$classroomId = $_GET['classroom_id'];

// Check if user is a teacher or a student
$isTeacher = isset($_SESSION['teacher_id']);
$isStudent = isset($_SESSION['student_id']);

if (isset($_GET['delete_assignment_id'])) {
    $assignmentIdToDelete = $_GET['delete_assignment_id'];
    $classroomId = $_GET['classroom_id'];

    $pdo->beginTransaction();
    try {
        // Delete from posted_in
        $stmtPostedIn = $pdo->prepare("DELETE FROM posted_in WHERE p_assignment_id = :assignmentId");
        $stmtPostedIn->bindParam(':assignmentId', $assignmentIdToDelete, PDO::PARAM_INT);
        $stmtPostedIn->execute();

        // Delete from assignment_files
        $stmtFiles = $pdo->prepare("DELETE FROM assignment_files WHERE af_assignment_id = :assignmentId");
        $stmtFiles->bindParam(':assignmentId', $assignmentIdToDelete, PDO::PARAM_INT);
        $stmtFiles->execute();

        // Delete from assignment
        $stmtAssignment = $pdo->prepare("DELETE FROM assignment WHERE assignment_id = :assignmentId");
        $stmtAssignment->bindParam(':assignmentId', $assignmentIdToDelete, PDO::PARAM_INT);
        $stmtAssignment->execute();

        $pdo->commit();
        header("Location: http://localhost/gcc/classroom_home.php?classroom_id=" . $classroomId . "&view=post");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Failed to delete assignment: " . $e->getMessage();
    }
}

// If form is submitted by a teacher, process the assignment upload
if ($isTeacher && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['postAssignment'])) {
    $assignmentDescription = $_POST['assignmentDescription'];
    $assignmentDueDate = $_POST['assignmentDueDate'];

    if (isset($_FILES['assignmentFile']) && $_FILES['assignmentFile']['error'] === UPLOAD_ERR_OK) {
        $fileData = file_get_contents($_FILES['assignmentFile']['tmp_name']);
        
        $pdo->beginTransaction();
        try {
            // Insert assignment into 'assignment' table
            $stmtAssignment = $pdo->prepare("
                INSERT INTO assignment (assignment_description, assignment_due_date)
                VALUES (:description, :dueDate)
            ");
            $stmtAssignment->bindParam(':description', $assignmentDescription, PDO::PARAM_STR);
            $stmtAssignment->bindParam(':dueDate', $assignmentDueDate, PDO::PARAM_STR);
            $stmtAssignment->execute();

            // Get last inserted assignment ID
            $assignmentId = $pdo->lastInsertId();

            // Insert file into 'assignment_files' table
            $stmtFile = $pdo->prepare("
                INSERT INTO assignment_files (af_assignment_id, assignment_file)
                VALUES (:assignmentId, :fileData)
            ");
            $stmtFile->bindParam(':assignmentId', $assignmentId, PDO::PARAM_INT);
            $stmtFile->bindParam(':fileData', $fileData, PDO::PARAM_LOB);
            $stmtFile->execute();

            // Insert entry into posted_in
            $stmtPostedIn = $pdo->prepare("
                INSERT INTO posted_in (p_classroom_id, p_assignment_id)
                VALUES (:classroomId, :assignmentId)
            ");
            $stmtPostedIn->bindParam(':classroomId', $classroomId, PDO::PARAM_INT);
            $stmtPostedIn->bindParam(':assignmentId', $assignmentId, PDO::PARAM_INT);
            $stmtPostedIn->execute();

            $pdo->commit();
            echo "Assignment posted successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Failed to post assignment: " . $e->getMessage();
        }
    } else {
        echo "Error: Please upload a valid file.";
    }
}

// Display the form for teachers to post an assignment
if ($isTeacher) {
    echo '<div class="post-new-assignment">';
    echo '<h3>Post New Assignment</h3>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="hidden" name="postAssignment" value="1">';
    echo '<label for="assignmentDescription">Description:</label><br>';
    echo '<textarea id="assignmentDescription" name="assignmentDescription" required></textarea><br><br>';
    echo '<label for="assignmentDueDate">Due Date:</label><br>';
    echo '<input type="date" id="assignmentDueDate" name="assignmentDueDate" required><br><br>';
    echo '<label for="assignmentFile">Upload File:</label><br>';
    echo '<input type="file" id="assignmentFile" name="assignmentFile" accept=".pdf,.doc,.docx" required><br><br>';
    echo '<button type="submit">Post Assignment</button>';
    echo '</form><hr>';
    echo '</div>';
}

// Display the assignments and submission form for students
echo "<h3 class='assignments-header'>Assignments</h3>";
$stmtAssignments = $pdo->prepare("
    SELECT a.assignment_id, a.assignment_description, a.assignment_due_date
    FROM assignment a
    LEFT JOIN assignment_files af ON a.assignment_id = af.af_assignment_id
");
$stmtAssignments->execute();
$assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);

if ($assignments) {
    echo '<div class="posted-content-holder">';
    foreach ($assignments as $assignment) {
        echo '<div class="posted-single-assignment">';
        echo '<h4>Assignment ' . htmlspecialchars($assignment['assignment_id']) . '</h4>';
        echo '<p><strong>Description:</strong> ' . htmlspecialchars($assignment['assignment_description']) . '</p>';
        echo '<p><strong>Due Date:</strong> ' . htmlspecialchars($assignment['assignment_due_date']) . '</p>';
        
        echo '<p><a href="download.php?assignment_id=' . urlencode($assignment['assignment_id']) . '" class="download-link">Download Assignment File</a></p>';

        // Display delete button for teachers
        if ($isTeacher) {
            echo '<form method="get" action="classroom_post.php" class="post-delete-button">';
            echo '<input type="hidden" name="classroom_id" value="' . htmlspecialchars($classroomId) . '">';
            echo '<input type="hidden" name="delete_assignment_id" value="' . htmlspecialchars($assignment['assignment_id']) . '">';
            echo '<button type="submit" class="delete-button">Delete Assignment</button>';
            echo '</form>';
        }

        // If logged in as a student, show the submission form
        if ($isStudent) {
            echo '<div class="submit-assignment">';
            echo '<h5>Submit Assignment</h5>';
            echo '<form method="post" enctype="multipart/form-data">';
            echo '<input type="hidden" name="assignment_id" value="' . htmlspecialchars($assignment['assignment_id']) . '">';
            echo '<label for="submissionDescription">Description:</label><br>';
            echo '<textarea id="submissionDescription" name="submissionDescription" required></textarea><br><br>';
            echo '<label for="submissionFile">Upload File:</label><br>';
            echo '<input type="file" id="submissionFile" name="submissionFile" accept=".pdf" required><br><br>';
            echo '<button type="submit" name="submitAssignment" class="submit-button">Submit Assignment</button>';
            echo '</form><hr>';
            echo '</div>';
        }
        echo '</div>';
    }
    echo '</div>';

} else {
    echo '<p>No assignments posted yet.</p>';
}

// Process student assignment submission
if ($isStudent && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitAssignment'])) {
    $assignmentId = $_POST['assignment_id'];
    $studentId = $_SESSION['student_id'];
    $submissionDescription = $_POST['submissionDescription'];
    date_default_timezone_set('Asia/Kolkata');
    $submissionDate = date('Y-m-d H:i:s'); // Automatically takes the current timestamp

    if (isset($_FILES['submissionFile']) && $_FILES['submissionFile']['error'] === UPLOAD_ERR_OK) {
        $fileData = file_get_contents($_FILES['submissionFile']['tmp_name']);

        $pdo->beginTransaction();
        try {
            // Insert submission into 'submission' table
            $stmtSubmission = $pdo->prepare("
                INSERT INTO submission (s_assignment_id, s_student_id, submission_description, submission_date, submission_file)
                VALUES (:assignmentId, :studentId, :description, :date, :fileData)
            ");
            $stmtSubmission->bindParam(':assignmentId', $assignmentId, PDO::PARAM_INT);
            $stmtSubmission->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $stmtSubmission->bindParam(':description', $submissionDescription, PDO::PARAM_STR);
            $stmtSubmission->bindParam(':date', $submissionDate, PDO::PARAM_STR);
            $stmtSubmission->bindParam(':fileData', $fileData, PDO::PARAM_LOB);
            $stmtSubmission->execute();

            $stmtProc = $pdo->prepare("
                CALL delete_old_submissions(:assignmentId, :studentId, :submissionDate)
            ");
            $stmtProc->bindParam(':assignmentId', $assignmentId, PDO::PARAM_INT);
            $stmtProc->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $stmtProc->bindParam(':submissionDate', $submissionDate, PDO::PARAM_STR);
            $stmtProc->execute();

            $pdo->commit();
            echo "Assignment submitted successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Failed to submit assignment: " . $e->getMessage();
        }
    } else {
        echo "Error: Please upload a valid file.";
    }
}
?>

<head>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>