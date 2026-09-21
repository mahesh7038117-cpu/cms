<?php
include '../db.php';
session_start();

// Verify security access profile
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Check database connection
if (!isset($conn) || !$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Retrieve dynamic transcripts
$query = "SELECT m.subject_name, m.marks_obtained, m.total_marks, m.exam_date, t.name as teacher_name 
          FROM Marks m 
          JOIN Teacher t ON m.teacher_id = t.teacher_id 
          WHERE m.student_id = '$student_id' 
          ORDER BY m.exam_date DESC";
$result = mysqli_query($conn, $query);

// Aggregate grade distributions
$summary_query = "SELECT SUM(marks_obtained) as total_earned, SUM(total_marks) as total_possible 
                  FROM Marks WHERE student_id = '$student_id'";
$summary_result = mysqli_query($conn, $summary_query);
$summary = mysqli_fetch_assoc($summary_result);

$earned = $summary['total_earned'] ?? 0;
$possible = $summary['total_possible'] ?? 0;
$percentage = $possible > 0 ? round(($earned / $possible) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Marks Transcript</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f9; }
        .summary-card { background: white; padding: 15px; width: 320px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 60%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #007bff; color: white; }
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h2>📈 My Academic Progress Report</h2>
    <p><a href="../dashboards/dashboard.php">⬅️ Back to Dashboard</a></p>

    <div class="summary-card">
        <p><strong>Total Marks Accumulated:</strong> <?php echo "$earned / $possible"; ?></p>
        <p><strong>Cumulative Percentage:</strong> <?php echo $percentage; ?>%</p>
        <p><strong>Overall Standing:</strong> 
            <span class="<?php echo $percentage >= 40 ? 'pass' : 'fail'; ?>">
                <?php echo $percentage >= 40 ? 'PASS' : 'FAIL'; ?>
            </span>
        </p>
    </div>

    <h3>Detailed Subject Breakdown</h3>
    <table>
        <tr>
            <th>Date</th>
            <th>Subject</th>
            <th>Obtained Marks</th>
            <th>Out of</th>
            <th>Percentage</th>
            <th>Evaluated By</th>
        </tr>
        <?php if (mysqli_num_rows($result) > 0) { 
            while ($row = mysqli_fetch_assoc($result)) { 
                $sub_pct = round(($row['marks_obtained'] / $row['total_marks']) * 100, 1);
                ?>
            <tr>
                <td><?php echo $row['exam_date']; ?></td>
                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                <td><?php echo $row['marks_obtained']; ?></td>
                <td><?php echo $row['total_marks']; ?></td>
                <td><strong><?php echo $sub_pct; ?>%</strong></td>
                <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
            </tr>
            <?php } 
        } else { ?>
            <tr>
                <td colspan="6">No graded evaluations published yet.</td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
