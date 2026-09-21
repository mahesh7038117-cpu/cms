<?php
include '../db.php';
session_start();

$conn = $conn ?? null;
if (!($conn instanceof mysqli)) {
    die('Database connection is unavailable.');
}

// Enforce Student-only access
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// FIX: Change 'id' to 'user_id' to match your secure login session setup
$student_id = $_SESSION['user_id'] ?? null;
if (!$student_id) {
    die('Student ID is missing from the session. Please log in again.');
}

// 1. Fetch individual daily attendance logs using Prepared Statements
$query = "SELECT a.date, a.status, t.name as teacher_name 
          FROM Attendance a 
          JOIN Teacher t ON a.teacher_id = t.teacher_id 
          WHERE a.student_id = ? 
          ORDER BY a.date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// 2. Calculate metrics (Total days, days present) using Prepared Statements
$stats_query = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days
                FROM Attendance WHERE student_id = ?";

$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $student_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

$total_days = $stats['total_days'] ?? 0;
$present_days = $stats['present_days'] ?? 0;
$percentage = $total_days > 0 ? round(($present_days / $total_days) * 100, 1) : 0;

$stats_stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Attendance</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        .summary-box { background: white; padding: 15px; width: 300px; border-radius: 5px;
         box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 50%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #007bff; color: white; }
        .Present { color: green; font-weight: bold; }
        .Absent { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h2> My Attendance Summary</h2>
    <p><a href="../dashboards/dashboard.php"> Back to Dashboard</a></p>

    <div class="summary-box">
        <p><strong>Total Working Days:</strong> <?php echo $total_days; ?></p>
        <p><strong>Days Present:</strong> <?php echo $present_days; ?></p>
        <p><strong>Attendance Rate:</strong> <?php echo $percentage; ?>%
    </div>

    <h3>Detailed History</h3>
    <table>
        <tr>
            <th>Date</th>
            <th>Marked By</th>
            <th>Status</th>
        </tr>
        <?php if ($result && $result->num_rows > 0) { 
            while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['date']); ?></td>
                <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                <td class="<?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></td>
            </tr>
            <?php } 
        } else { ?>
            <tr>
                <td colspan="3">No attendance records found yet.</td>
            </tr>
        <?php } 
        $stmt->close();
        ?>
    </table>
</body>
</html>
