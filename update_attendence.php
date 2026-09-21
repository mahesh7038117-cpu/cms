<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] == 'student') {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

$teacher_id = $_SESSION['user_id'];
$selected_date = $_GET['date'] ?? date('Y-m-d');
$message = "";

// Process Updates
if (isset($_POST['save_changes'])) {
    $date = $_POST['date'];
    $status_array = $_POST['status'] ?? [];

    foreach ($status_array as $student_id => $status) {
        $stmt = $conn->prepare("INSERT INTO Attendance (student_id, teacher_id, date, status) 
                                VALUES (?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE status = ?, teacher_id = ?");
        $stmt->bind_param("iisssi", $student_id, $teacher_id, $date, $status, $status, $teacher_id);
        $stmt->execute();
        $stmt->close();
    }
    $message = "<div class='success-box'>Attendance updated successfully for $date!</div>";
    $selected_date = $date; 
}

// Fetch all students alongside their attendance log for the selected date
$query = "SELECT s.student_id, s.name, a.status 
          FROM Student s 
          LEFT JOIN Attendance a ON s.student_id = a.student_id AND a.date = ? 
          ORDER BY s.name ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$students_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review & Update Attendance</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: linear-gradient(135deg, #31e4cc, #4a90e2); padding: 40px 20px; min-height: 100vh; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        h2 { text-align: center; margin-bottom: 25px; color: #333; }
        .filter-section { background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #e2e8f0; display: flex; gap: 10px; align-items: center; }
        input[type="date"], button { padding: 10px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }
        th, td { border: 1px solid #cbd5e1; padding: 12px; text-align: left; }
        th { background: #4a90e2; color: white; }
        .btn-submit { width: 100%; background: #28a745; color: white; font-weight: bold; border: none; cursor: pointer; padding: 12px; }
        .btn-submit:hover { background: #218838; }
        .success-box { background: #eefbf3; color: #1e7e34; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #4a90e2; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>✏️ Review & Update Attendance</h2>
        <?php echo $message; ?>

        <!-- Date Selection Form -->
        <form method="GET" action="" class="filter-section">
            <label><strong>Target Date:</strong></label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" required>
            <button type="submit">Load Sheet</button>
        </form>

        <!-- Attendance Sheet Update Form -->
        <form method="POST" action="">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Status Record</th>
                </tr>
                <?php while ($row = $students_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td>
                            <label><input type="radio" name="status[<?php echo $row['student_id']; ?>]" value="Present" <?php echo ($row['status'] !== 'Absent') ? 'checked' : ''; ?>> Present</label> &nbsp;&nbsp;
                            <label><input type="radio" name="status[<?php echo $row['student_id']; ?>]" value="Absent" <?php echo ($row['status'] === 'Absent') ? 'checked' : ''; ?>> Absent</label>
                        </td>
                    </tr>
                <?php endwhile; $stmt->close(); ?>
            </table>
            <button type="submit" name="save_changes" class="btn-submit">Save Attendance Changes</button>
        </form>
        <a class="back-link" href="../dashboards/dashboard.php">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
