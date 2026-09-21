<?php include '../db.php';
session_start();

$conn = $conn ?? null;
if (!($conn instanceof mysqli)) {
    die('Database connection is unavailable.');
}

// Enforce Teacher-only access
if (!isset($_SESSION['username']) || $_SESSION['role'] == 'student') {
    header("Location: ../login.php");
    exit();
}

// FIX: Update 'user_id' if your session uses a different key name (e.g., 'teacher_id')
$teacher_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
if (!$teacher_id) {
    die('Teacher ID is missing from the session. Please log in again.');
}

$message = ""; 

// Handle Attendance Submission
if (isset($_POST['submit_attendance'])) {
    $date = $_POST['date'];
    $status_array = $_POST['status'] ?? [];
    
    // STEP 1: Check if attendance has already been recorded for this date
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM Attendance WHERE date = ?");
    $check_stmt->bind_param("s", $date);
    $attendance_count = 0;
    $check_stmt->execute();
    $check_stmt->bind_result($attendance_count);
    $check_stmt->fetch();
    $check_stmt->close();

    // STEP 2: If records exist, block the submission and show an error
    if ($attendance_count > 0) {
        $message = "<p style='color:red;'>⚠️ Attendance has already been recorded for $date. Duplicate entries are not allowed!</p>";
    } else {
        // STEP 3: If no records exist, proceed with inserting the attendance safely
        $stmt = $conn->prepare("INSERT INTO Attendance (student_id, teacher_id, date, status) VALUES (?, ?, ?, ?)");
        
        if ($stmt) {
            foreach ($status_array as $student_id => $status) {
                $stmt->bind_param("iiss", $student_id, $teacher_id, $date, $status);
                $stmt->execute();
            }
            $stmt->close();
            $message = "<p style='color:green;'>🎉 Attendance recorded successfully for $date!</p>";
        } else {
            $message = "<p style='color:red;'>Database error: failed to prepare statement.</p>";
        }
    }
}

// Fetch all students to build the checklist
$students_result = mysqli_query($conn, "SELECT student_id, name FROM Student ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enter Attendance</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        table { width: 50%; border-collapse: collapse; margin-top: 15px; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #28a745; color: white; }
        .btn { background: #28a745; color: white; padding: 10px 15px; border: none; cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>
    <h2>📋 Mark Student Attendance</h2>
    <p><a href="../dashboards/dashboard.php">⬅️ Back to Dashboard</a></p>
    <?php echo $message; ?>
    <form method="POST" action="">
        <label><strong>Select Date:</strong></label>
        <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required><br><br>
        <table>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Status</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($students_result)) { ?>
            <tr>
                <td><?php echo $row['student_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td>
                    <label><input type="radio" name="status[<?php echo $row['student_id']; 
                    ?>]" value="Present" checked> Present</label>
                    <label><input type="radio" name="status[<?php echo $row['student_id'];
                     ?>]" value="Absent"> Absent</label>
                </td>
            </tr>
            <?php } ?>
        </table><br>
        <button type="submit" name="submit_attendance" class="btn">Submit Attendance</button>
    </form>
</body>
</html>
