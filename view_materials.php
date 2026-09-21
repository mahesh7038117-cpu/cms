<?php
require_once __DIR__ . '/../db.php';
session_start();

// Block unauthenticated viewers
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header("Location:../login.php");
    exit();
}

// Check database connection
if (!isset($conn) || !$conn) {
    die("Database connection failed");
}

// Fetch all materials joined with teacher details
$query = "SELECT m.title, m.description, m.file_path, m.uploaded_at, t.name as teacher_name 
          FROM StudyMaterials m
          JOIN Teacher t ON m.teacher_id = t.teacher_id
          ORDER BY m.uploaded_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Material Logs</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 40px; background: #f4f6f9; color: #333; }
        h2 { color: #95a5aefd; margin-bottom: 5px; }
        .subtitle { color: #666; margin-bottom: 25px; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; font-weight: bold; }
        .materials-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        .material-card { background: grey; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 4px solid #007bff; display: flex; flex-direction: column; justify-content: space-between; }
        .material-card h3 { margin-top: 0; color: #222; font-size: 18px; }
        .meta-text { font-size: 12px; color: #777; margin-bottom: 12px; }
        .description { font-size: 14px; color: #555; margin-bottom: 20px; line-height: 1.5; flex-grow: 1; }
        .download-btn { display: inline-block; text-align: center; background: #007bff; color: white; padding: 10px; border-radius: 4px; text-decoration: none; font-weight: bold; transition: background 0.2s; }
        .download-btn:hover { background: #0056b3; }
        .empty-msg { background: white; padding: 20px; text-align: center; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); grid-column: 1 / -1; }
    </style>
</head>
<body>

    <a href="../dashboards/dashboard.php" class="back-link">⬅️ Back to Dashboard</a>
    <h2>📚 Study Material Logs Reference</h2>
    <p class="subtitle">Download lecture slides, assignments, and documents published by your instructors.</p>

    <div class="materials-grid">
        <?php if (mysqli_num_rows($result) > 0) { 
            while ($row = mysqli_fetch_assoc($result)) { 
                $date_formatted = date('M d, Y', strtotime($row['uploaded_at']));
                ?>
                <div class="material-card">
                    <div>
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <div class="meta-text">
                            Shared by: <strong><?php echo htmlspecialchars($row['teacher_name']); ?></strong> | On: <?php echo $date_formatted; ?>
                        </div>
                        <p class="description">
                            <?php echo !empty($row['description']) ? nl2br(htmlspecialchars($row['description'])) : "<em>No additional details provided.</em>"; ?>
                        </p>
                    </div>
                    <!-- Direct download target locator links -->
                    <a href="<?php echo htmlspecialchars($row['file_path']); ?>" download class="download-btn">⬇️ Download Document</a>
                </div>
            <?php } 
        } else { ?>
            <div class="empty-msg">
                <p style="font-size: 16px; color: #666;">No study reference logs have been posted to this index platform yet.</p>
            </div>
        <?php } ?>
    </div>

</body>
</html>
