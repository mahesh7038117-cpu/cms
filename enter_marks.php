<?php
include '../db.php';
/** @var mysqli $conn */
session_start();

// Verify security access profile
if (!isset($_SESSION['username']) || $_SESSION['role'] == 'student') {
    header("Location: ../login.php");
    exit();
}

$current_role = $_SESSION['role'];
$user_id = intval($_SESSION['user_id']);
$teacher_id = 0; 

if ($current_role !== 'admin') {
    // If it's a regular teacher, use their real ID
    $teacher_id = $user_id; 
} else {
    // FIX FOR FOREIGN KEY CONSTRAINT:
    // Since an admin cannot use '0', we will look up the very first available teacher ID 
    // from the Teacher table to act as a valid placeholder so the database doesn't crash.
    $fallback_result = mysqli_query($conn, "SELECT teacher_id FROM Teacher LIMIT 1");
    if ($row = mysqli_fetch_assoc($fallback_result)) {
        $teacher_id = intval($row['teacher_id']);
    } else {
        // If the teacher table is completely empty, we fallback to the admin's user_id
        $teacher_id = $user_id; 
    }
}

$message = "";

if (isset($_POST['submit_marks'])) {
    $subject = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $exam_date = $_POST['exam_date'];
    $total_marks = intval($_POST['total_marks']);
    $marks_array = $_POST['marks_obtained']; // Array mapping student_id => numeric marks

    $validation_error = false;

    // First Pass: Validate ALL scores in PHP before saving anything
    foreach ($marks_array as $student_id => $score) {
        if ($score !== '') {
            $score = intval($score);
            if ($score < 0 || $score > $total_marks) {
                $validation_error = true;
                break;
            }
        }
    }

    // Process or reject based on validation results
    if ($validation_error) {
        $message = "<p style='color:red; font-weight:bold;'>❌ Error: Process stopped! One or more students have scores less than 0 or higher than Total Weightage.</p>";
    } else {
        // Run database queries only if all data passed validation
        foreach ($marks_array as $student_id => $score) {
            if ($score !== '') {
                $score = intval($score);
                
                // This will now use a structurally valid ID that satisfies your constraint rules
                $query = "INSERT INTO Marks (student_id, teacher_id, subject_name, marks_obtained, total_marks, exam_date) 
                          VALUES ('$student_id', '$teacher_id', '$subject', '$score', '$total_marks', '$exam_date')
                          ON DUPLICATE KEY UPDATE marks_obtained='$score', total_marks='$total_marks', teacher_id='$teacher_id'";
                mysqli_query($conn, $query);
            }
        }
        
        $actor_text = ($current_role === 'admin') ? "Admin (System Controlled)" : "Teacher";
        $message = "<p style='color:green;'>👍 Exam grades logged successfully by $actor_text for $subject!</p>";
    }
}

// Fetch directory roster
$students_result = mysqli_query($conn, "SELECT student_id, name FROM Student ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enter Marks</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        .form-panel { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 15px; }
        table { width: 50%; border-collapse: collapse; margin-top: 15px; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #28a745; color: white; }
        input[type="number"] { width: 80px; padding: 5px; }
        .btn { background: #28a745; color: white; padding: 10px 15px; border: none; cursor: pointer; border-radius: 4px; }
    </style>

    <!-- Frontend JavaScript Validation -->
    <script>
    function validateMarksForm() {
        var totalMarksInput = document.getElementById('total_marks');
        var totalMarks = parseInt(totalMarksInput.value, 10);
        
        var markInputs = document.querySelectorAll('.student-score-input');
        
        for (var i = 0; i < markInputs.length; i++) {
            var valStr = markInputs[i].value;
            
            if (valStr !== '') {
                var currentScore = parseInt(valStr, 10);
                
                if (currentScore < 0) {
                    alert("Error: Marks cannot be negative!");
                    markInputs[i].focus();
                    return false;
                }
                
                if (currentScore > totalMarks) {
                    alert("Error: Obtained marks cannot be greater than Total Weightage (" + totalMarks + ")!");
                    markInputs[i].focus();
                    return false; 
                }
            }
        }
        return true; 
    }
    </script>
</head>
<body>
    <h2>✍️ Record Evaluation Grades</h2>
    <p><a href="../dashboards/dashboard.php">⬅️ Back to Dashboard</a></p>
    <?php echo $message; ?>

    <div class="form-panel">
        <form method="POST" action="" onsubmit="return validateMarksForm()">
            <label><strong>Subject Name:</strong></label>
            <input type="text" name="subject_name" placeholder="e.g. Mathematics" required> &nbsp;&nbsp;

            <label><strong>Exam Date:</strong></label>
            <input type="date" name="exam_date" value="<?php echo date('Y-m-d'); ?>" required> &nbsp;&nbsp;

            <label><strong>Total Weightage:</strong></label>
            <input type="number" id="total_marks" name="total_marks" value="100" min="1" required><br><br>

            <table>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Obtained Marks</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($students_result)) { ?>
                <tr>
                    <td><?php echo $row['student_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                        <input type="number" 
                               class="student-score-input" 
                               name="marks_obtained[<?php echo $row['student_id']; ?>]" 
                               min="0" 
                               placeholder="Score">
                    </td>
                </tr>
                <?php } ?>
            </table><br>
            <button type="submit" name="submit_marks" class="btn">Publish Grades</button>
        </form>
    </div>
</body>
</html>
