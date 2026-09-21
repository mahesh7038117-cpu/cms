<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_POST['add_schedule'])) {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $target_role = $_POST['target_role'];
    $schedule_date = $_POST['schedule_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $stmt = $conn->prepare("INSERT INTO ScheduleTimeline (title, description, target_role, schedule_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $description, $target_role, $schedule_date, $start_time, $end_time);
    
    if ($stmt->execute()) {
        $message = "<div class='success-box'>Timeline event added successfully!</div>";
    } else {
        $message = "<div class='error-box'>Failed to add timeline event.</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Timeline</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: linear-gradient(135deg, #31e4cc, #4a90e2); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        textarea { height: 80px; resize: vertical; }
        button { width: 100%; padding: 12px; background: #4a90e2; border: none; color: #fff; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; }
        button:hover { background: #357abd; }
        .success-box { background: #eefbf3; color: #1e7e34; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .error-box { background: #ffeef0; color: #d9383a; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #4a90e2; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🗓️ Create Schedule Event</h2>
        <?php echo $message; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Event Title</label>
                <input type="text" name="title" required placeholder="e.g., Final Term Exam">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required placeholder="Provide details about the event..."></textarea>
            </div>
            <div class="form-group">
                <label>Target Audience</label>
                <select name="target_role">
                    <option value="all">Everyone (All)</option>
                    <option value="student">Students Only</option>
                    <option value="teacher">Teachers Only</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="schedule_date" required>
            </div>
            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="start_time" required>
            </div>
            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="end_time" required>
            </div>
            <button type="submit" name="add_schedule">Publish Event</button>
        </form>
        <a class="back-link" href="./dashboards/dashboard.php">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
