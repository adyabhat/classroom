<?php
include 'db.php';

if (isset($_GET['assignment_id'])) {
    $assignmentId = $_GET['assignment_id'];
    
    $stmt = $pdo->prepare("
        SELECT assignment_file 
        FROM assignment_files 
        WHERE af_assignment_id = :assignmentId
    ");
    $stmt->bindParam(':assignmentId', $assignmentId, PDO::PARAM_INT);
    $stmt->execute();
    $fileData = $stmt->fetchColumn();
    
    if ($fileData) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="assignment_' . $assignmentId . '.pdf"');
        echo $fileData;
        exit();
    } else {
        echo "File not found.";
    }
} elseif (isset($_GET['submission_id'])) {
    $submissionId = $_GET['submission_id'];

    $stmt = $pdo->prepare("
        SELECT submission_file
        FROM submission
        WHERE submission_id = :submissionId
    ");
    $stmt->bindParam(':submissionId', $submissionId, PDO::PARAM_INT);
    $stmt->execute();
    $fileData = $stmt->fetchColumn();

    if ($fileData) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="submission_' . $submissionId . '.pdf"');
        echo $fileData;
        exit();
    } else {
        echo "File not found.";
    }
}

else {
    echo "Invalid request.";
}
?>