<?php
include '../db.php';
session_start();

// Block unauthorized non-teacher entry logs
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

// Verify database connection
if (!isset($conn) || !$conn) {
    die("Database connection error.");
}

$teacher_id = $_SESSION['user_id']; // Match your secure login variable configuration key
$message = "";

if (isset($_POST['upload'])) {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    
    // File upload management block
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Safely establish folder directory index 
    }
    
    $file_name = basename($_FILES["material_file"]["name"]);
    $clean_file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", $file_name); // Prevent naming conflict bugs
    $target_file_path = $target_dir . $clean_file_name;
    $file_type = strtolower(pathinfo($target_file_path, PATHINFO_EXTENSION));
    
    // Safety check boundaries
    $allowed_types = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'png', 'jpg');
    
    if (empty($title) || empty($_FILES["material_file"]["name"])) {
        $message = "<p style='color:red;'>Please populate all operational data values.</p>";
    } elseif (!in_array($file_type, $allowed_types)) {
        $message = "<p style='color:red;'>Invalid file type! Allowed extensions: " . implode(', ', $allowed_types) . "</p>";
    } elseif ($_FILES["material_file"]["size"] > 10485760) { // Limit size threshold up to 10MB
        $message = "<p style='color:red;'>The file is too large! Maximum limit size is 10MB.</p>";
    } else {
        if (move_uploaded_file($_FILES["material_file"]["tmp_name"], $target_file_path)) {
            // Prepared statements to securely persist records
            $stmt = $conn->prepare("INSERT INTO StudyMaterials (teacher_id, title, description, file_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $teacher_id, $title, $description, $target_file_path);
            
            if ($stmt->execute()) {
                $message = "<p style='color:green;'>Material resource log published successfully!</p>";
            } else {
                $message = "<p style='color:red;'>Database storage execution anomaly encountered.</p>";
            }
            $stmt->close();
        } else {
            $message = "<p style='color:red;'>System storage transfer operation failure.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Study Material</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 40px; background: #f4f6f9; color: #333; }
        .upload-container { max-width: 500px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { color: #28a745; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 100px; resize: vertical; }
        .btn { background: #28a745; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%; }
        .btn:hover { background: #218838; }
        .back-link { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #007bff; }
    </style>
</head>
<body>

    <div class="upload-container">
        <a href="../dashboards/dashboard.php" class="back-link">⬅️ Back to Dashboard</a>
        <h2>📤 Upload Educational Material</h2>
        <?php echo $message; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Material / Topic Title</label>
                <input type="text" name="title" placeholder="e.g. Chapter 1: Introduction to PHP" required>
            </div>
            <div class="form-group">
                <label>Description / Study Instructions</label>
                <textarea name="description" placeholder="Briefly summarize what this study guide covers..."></textarea>
            </div>
            <div class="form-group">
                <label>Select Document File (Max 10MB)</label>
                <input type="file" name="material_file" required>
            </div>
            <button type="submit" name="upload" class="btn">Publish to Logs</button>
        </form>
    </div>

</body>
</html>
