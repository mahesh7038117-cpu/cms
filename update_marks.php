<?php
include '../db.php';
session_start();

if (!isset($conn)) {
    die("Database connection failed. Please contact administrator.");
}

// Verify security access profile (Allows both admin and teacher roles now)
if (!isset($_SESSION['username']) || $_SESSION['role'] == 'student') {
    header("Location: ../login.php");
    exit();
}

// 1. Separate tracking logic for Admin vs Teacher
$current_role = $_SESSION['role']; 
$user_id = $_SESSION['user_id'];

$teacher_id = ($current_role === 'admin') ? 0 : $user_id; 
$actor_label = ($current_role === 'admin') ? "Admin" : "Teacher";

$selected_subject = $_GET['subject_name'] ?? '';
$selected_date = $_GET['exam_date'] ?? date('Y-m-d');
$message = "";

// Process Evaluation Score Overwrites
if (isset($_POST['save_marks'])) {
    $subject = trim($_POST['subject_name']);
    $exam_date = $_POST['exam_date'];
    $total_marks = intval($_POST['total_marks']);
    $marks_array = $_POST['marks_obtained'] ?? [];

    $validation_error = false;

    // PHP Validation Loop
    foreach ($marks_array as $student_id => $score) {
        if ($score !== '') {
            $score = intval($score);
            if ($score < 0 || $score > $total_marks) {
                $validation_error = true;
                break;
            }
        }
    }

    if ($validation_error) {
        $message = "<div class='success-box' style='background:#fde8e8; color:#e53e3e;'>❌ Error: Update canceled. Entries violate structural marking limits.</div>";
    } else {
        foreach ($marks_array as $student_id => $score) {
            if ($score !== '') {
                $score = intval($score);
                $stmt = $conn->prepare("INSERT INTO Marks (student_id, teacher_id, subject_name, marks_obtained, total_marks, exam_date) 
                                        VALUES (?, ?, ?, ?, ?, ?) 
                                        ON DUPLICATE KEY UPDATE marks_obtained = ?, total_marks = ?, teacher_id = ?");
                $stmt->bind_param("iissisiii", $student_id, $teacher_id, $subject, $score, $total_marks, $exam_date, $score, $total_marks, $teacher_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        // Personalized confirmation showing who modified the grades
        $message = "<div class='success-box'>Grades adjusted successfully by $actor_label!</div>";
    }
    $selected_subject = $subject;
    $selected_date = $exam_date;
}

// Fetch lists of marks 
$students_data = [];
$total_marks_val = 100;

if (!empty($selected_subject)) {
    $query = "SELECT s.student_id, s.name, m.marks_obtained, m.total_marks 
              FROM Student s 
              LEFT JOIN Marks m ON s.student_id = m.student_id AND m.subject_name = ? AND m.exam_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $selected_subject, $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students_data[] = $row;
        if (isset($row['total_marks'])) {
            $total_marks_val = $row['total_marks'];
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review & Update Marks</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: linear-gradient(135deg, #31e4cc, #4a90e2); padding: 40px 20px; min-height: 100vh; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 650px; margin: 0 auto; }
        h2 { text-align: center; margin-bottom: 10px; color: #333; }
        .role-badge-container { text-align: center; margin-bottom: 20px; }
        .role-badge { display: inline-block; padding: 6px 12px; background: #edf2f7; color: #2d3748; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .filter-section { background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        input, button { padding: 10px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        th, td { border: 1px solid #cbd5e1; padding: 12px; text-align: left; }
        th { background: #28a745; color: white; }
        input[type="number"] { width: 90px; }
        .btn-submit { width: 100%; background: #28a745; color: white; font-weight: bold; border: none; cursor: pointer; padding: 12px; }
        .btn-submit:hover { background: #218838; }
        .success-box { background: #eefbf3; color: #1e7e34; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #4a90e2; text-decoration: none; }
    </style>

    <script>
    function validateUpdateForm() {
        var maxMarksInput = document.getElementById('total_marks');
        var maxMarks = parseInt(maxMarksInput.value, 10);
        var scoreInputs = document.querySelectorAll('.student-update-input');
        
        for (var i = 0; i < scoreInputs.length; i++) {
            var scoreVal = scoreInputs[i].value;
            if (scoreVal !== '') {
                var currentScore = parseInt(scoreVal, 10);
                if (currentScore < 0) {
                    alert("Error: Marks cannot be less than 0!");
                    scoreInputs[i].focus();
                    return false;
                }
                if (currentScore > maxMarks) {
                    alert("Error: Obtained marks cannot be larger than Total Max Marks (" + maxMarks + ")!");
                    scoreInputs[i].focus();
                    return false;
                }
            }
        }
        return true;
    }
    </script>
</head>
<body>
    <div class="container">
        <h2>✏️ Review & Update Exam Marks</h2>
        <div class="role-badge-container">
            <span class="role-badge">Logged in as: <?php echo htmlspecialchars(ucfirst($current_role)); ?></span>
        </div>
        
        <?php echo $message; ?>

        <!-- Selection Panel Filters -->
        <form method="GET" action="" class="filter-section">
            <input type="text" name="subject_name" value="<?php echo htmlspecialchars($selected_subject); ?>" placeholder="Subject Name (e.g. PHP)" required>
            <input type="date" name="exam_date" value="<?php echo htmlspecialchars($selected_date); ?>" required>
            <button type="submit">Load Grades</button>
        </form>

        <?php if (!empty($selected_subject)): ?>
        <form method="POST" action="" onsubmit="return validateUpdateForm()">
            <input type="hidden" name="subject_name" value="<?php echo htmlspecialchars($selected_subject); ?>">
            <input type="hidden" name="exam_date" value="<?php echo htmlspecialchars($selected_date); ?>">
            
            <div style="margin-bottom: 15px;">
                <label><strong>Total Max Marks:</strong></label>
                <input type="number" id="total_marks" name="total_marks" value="<?php echo $total_marks_val; ?>" min="1" required>
            </div>

            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Obtained Marks</th>
                </tr>
                <?php foreach ($students_data as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td>
                            <input type="number" 
                                   class="student-update-input" 
                                   name="marks_obtained[<?php echo $student['student_id']; ?>]" 
                                   value="<?php echo htmlspecialchars($student['marks_obtained'] ?? ''); ?>" 
                                   min="0" 
                                   placeholder="Not Graded">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <button type="submit" name="save_marks" class="btn-submit">Save Marks Changes</button>
        </form>
        <?php else: ?>
            <p style="text-align:center; color:#666; margin: 20px 0;">Please enter a subject name above to load data sheets.</p>
        <?php endif; ?>
        
        <a class="back-link" href="../dashboards/dashboard.php">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
