<?php
$classroomId = $_GET['classroom_id'];
$descriptionLength = 100; // Number of characters to display for assignment description

// Call the GetTruncatedAssignments stored procedure
$stmt = $pdo->prepare("CALL GetTruncatedAssignments(:classroomId, :descriptionLength)");
$stmt->bindParam(':classroomId', $classroomId, PDO::PARAM_INT);
$stmt->bindParam(':descriptionLength', $descriptionLength, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display posts
if ($posts) {
    echo '<div class="post-container">';
    foreach ($posts as $post) {
        echo '<div class="post">';
        echo '<p>' . htmlspecialchars($post['truncated_description']) . '</p>';
        echo '<small>Due on: ' . htmlspecialchars($post['assignment_due_date']) . '</small>';
        echo '</div>';
    }
    echo '</div>';
} else {
    echo '<p>No posts yet.</p>';
}

// Close the cursor to enable further SQL statements
$stmt->closeCursor();
?>

<head>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>