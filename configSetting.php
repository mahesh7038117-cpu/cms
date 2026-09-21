<?php
$conn = null;
require __DIR__ . '/db.php';
session_start();

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection could not be initialized.');
}

// Enforce strict Admin-only security profile
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// 1. PROCESS SYSTEM CONFIGURATION OVERWRITES
if (isset($_POST['save_settings'])) {
    $settings = [
        'college_name' => trim($_POST['college_name']),
        'academic_semester' => trim($_POST['academic_semester']),
        'system_status' => $_POST['system_status']
    ];

    $success = true;
    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO SystemSettings (setting_key, setting_value) VALUES (?, ?) 
                                ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        if (!$stmt->execute()) {
            $success = false;
        }
        $stmt->close();
    }

    if ($success) {
        $message = "<div class='alert success'>Global core architecture settings updated successfully!</div>";
    } else {
        $message = "<div class='alert error'>System update operation encountered a database anomaly.</div>";
    }
}

// 2. FETCH CURRENT SYSTEM REPOSITORY SETTINGS
$current_settings = [];
$result = mysqli_query($conn, "SELECT * FROM SystemSettings");
while ($row = mysqli_fetch_assoc($result)) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}

// Set system defaults if configurations are completely empty
$college_name = $current_settings['college_name'] ?? 'Global Technological College';
$academic_semester = $current_settings['academic_semester'] ?? 'Fall Semester 2026';
$system_status = $current_settings['system_status'] ?? 'Active';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - System Configuration</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background: linear-gradient(135deg, #31e4cc, #4a90e2); padding: 40px 20px; min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .config-container { background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { color: #2c3e50; margin-bottom: 20px; text-align: center; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; text-align: center; font-weight: bold; }
        .success { background: #eefbf3; color: #1e7e34; border-left: 4px solid #28a745; }
        .error { background: #ffeef0; color: #d9383a; border-left: 4px solid #d9383a; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 12px; font-weight: bold; color: #555; margin-bottom: 6px; text-transform: uppercase; }
        input[type="text"], select { width: 100%; padding: 12px; border: 1px solid #cccccc; border-radius: 4px; font-size: 14px; background: #fafafa; }
        input:focus, select:focus { outline: none; border-color: #4a90e2; background: #fff; }
        .btn-save { width: 100%; padding: 12px; background-color: #dc3545; border: none; border-radius: 4px; color: white; font-size: 16px; font-weight: bold; cursor: pointer; transition: background-color 0.3s ease; }
        .btn-save:hover { background-color: #bd2130; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #4a90e2; font-weight: bold; text-decoration: none; font-size: 14px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="config-container">
        <h2>🛠️ Global Core Architecture</h2>
        <?php echo $message; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Institution / College Name</label>
                <input type="text" name="college_name" value="<?php echo htmlspecialchars($college_name); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Current Academic Term / Semester</label>
                <input type="text" name="academic_semester" value="<?php echo htmlspecialchars($academic_semester); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Portal Registration System Status</label>
                <select name="system_status">
                    <option value="Active" <?php echo ($system_status == 'Active') ? 'selected' : ''; ?>>Active / Open</option>
                    <option value="Maintenance" <?php echo ($system_status == 'Maintenance') ? 'selected' : ''; ?>>Maintenance Mode / Locked</option>
                </select>
            </div>

            <button type="submit" name="save_settings" class="btn-save">Save System Settings</button>
        </form>

        <a href="./dashboards/dashboard.php" class="back-link">⬅️ Back to Control Dashboard</a>
    </div>

</body>
</html>
