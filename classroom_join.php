<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // echo $_POST['classroomCode'];
    // echo $_POST['newClassroomSubject'];
    if (isset($_POST['classroomCode']) && !isset($_POST['newClassroomSubject'])) {
        // Join Classroom Functionality
        $classroomCode = trim($_POST['classroomCode']);
        // echo $classroomCode;
        // Validate that the code is exactly 10 characters
        if (strlen($classroomCode) === 10) {
            // Check if the classroom code exists in the database
            $stmt = $pdo->prepare("SELECT classroom_id FROM classroom WHERE classroom_joining_code = :classroomCode");
            $stmt->bindParam(':classroomCode', $classroomCode);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                // Retrieve classroom ID
                $classroom = $stmt->fetch(PDO::FETCH_ASSOC);
                $classroomId = $classroom['classroom_id'];

                $pdo->beginTransaction();
                try {
                    if (isset($_SESSION['teacher_id'])) {
                        // echo $_SESSION['teacher_id'];
                        $stmtEntryToDb = $pdo->prepare(
                            "INSERT INTO teaches (t_teacher_id, t_classroom_id) VALUES (:teacher_id, :classroom_id)"
                        );
                        $stmtEntryToDb->bindParam(':teacher_id', $_SESSION['teacher_id'], PDO::PARAM_INT);
                        $stmtEntryToDb->bindParam(':classroom_id', $classroomId, PDO::PARAM_INT);
                        $stmtEntryToDb->execute();
                    } elseif (isset($_SESSION['student_id'])) {
                        $stmtEntryToDb = $pdo->prepare(
                            "INSERT INTO has (h_classroom_id, h_student_id) VALUES (:classroom_id, :student_id)"
                        );
                        $stmtEntryToDb->bindParam(':classroom_id', $classroomId, PDO::PARAM_INT);
                        $stmtEntryToDb->bindParam(':student_id', $_SESSION['student_id'], PDO::PARAM_INT);
                        $stmtEntryToDb->execute();
                    }
                    $pdo->commit();
                    // Redirect to classroom_home with classroom ID as a GET parameter
                    header("Location: classroom_home.php?classroom_id=" . urlencode($classroomId));
                    exit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    echo "Failed: " . $e->getMessage();
                }
            } else {
                echo "Invalid classroom code. Please try again.";
            }
        } else {
            echo "Classroom code must be exactly 10 characters.";
        }
    } elseif (isset($_POST['newClassroomSubject']) && isset($_POST['classroomCode'])) {
        // Create Classroom Functionality
        $newClassroomSubject = trim($_POST['newClassroomSubject']);
        $classroomCode = trim($_POST['classroomCode']);

        // Validate that the code is exactly 10 characters
        if (strlen($classroomCode) === 10 && isset($_SESSION['teacher_id'])) {
            // Insert the new classroom
            $stmt = $pdo->prepare(
                "INSERT INTO classroom (classroom_subject, classroom_joining_code, c_teacher_id) 
                VALUES (:subject, :classroomCode, :teacher_id)");
            $stmt->bindParam(':subject', $newClassroomSubject, PDO::PARAM_STR);
            $stmt->bindParam(':classroomCode', $classroomCode, PDO::PARAM_STR);
            $stmt->bindParam(':teacher_id', $_SESSION['teacher_id'], PDO::PARAM_INT);
            $stmt->execute();

            // Get the last inserted classroom ID
            $classroomId = $pdo->lastInsertId();

            // Redirect to classroom_home with classroom ID as a GET parameter
            header("Location: classroom_home.php?classroom_id=" . urlencode($classroomId));
            exit();
        } else {
            echo "Classroom code must be exactly 10 characters, and you must be logged in as a teacher to create a classroom.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Classroom</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="./images/gcc-logo.png" type="image/icon type">
</head>
<body>
    <?php include 'header.php'; ?>
    <div>
        <div class="join-classroom">
            <h2>Join Classroom</h2>
            <form method="post" action="classroom_join.php">
                <label for="classroomCode">Classroom Code (10 characters):</label><br>
                <input type="text" id="classroomCode" name="classroomCode" pattern=".{10}" required><br><br>
                <button type="submit">Join</button>
            </form>
        </div>
        <?php
        if (isset($_SESSION['teacher_id'])) {
            echo "<div class='create-classroom'>";
            echo "   <h2>Create Classroom</h2>";
            echo '    <form method="post" action="classroom_join.php">';
            echo '        <label for="newClassroom">Classroom Subject:</label><br>';
            echo '        <input type="text" id="newClassroomSubject" name="newClassroomSubject" required><br><br>';
            echo '        <label for="newClassroom">Classroom Code (10 characters):</label><br>';
            echo '        <input type="text" id="classroomCode" name="classroomCode" pattern=".{10}" required><br><br>';
            echo '        <button type="submit">Create</button>';
            echo '    </form>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>