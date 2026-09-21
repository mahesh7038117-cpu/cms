<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .nav { background: #007bff; padding: 15px; color: white; display: flex; justify-content: space-between; border-radius: 5px; }
        .nav a { color: #fff; text-decoration: none; font-weight: bold; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card h3 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
    </style>
</head>
<body>
    <div class="nav">

  
        
    
    <a href="../viewProfile.php" style="margin-right: 15px; color: white;">My Profile</a>
        <span>🎓 Welcome to Student Dashboard   <marquee behavior="alternate"><?php echo htmlspecialchars($_SESSION['username']); ?></marquee></span>
       <br>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="grid">
        <div class="card">
            <h3>📚 Study Materials</h3>
            <p>Access notes, files, and lectures uploaded by your class teachers.</p>
            <a href="../StudyMaterials/view_materials.php">View Material Logs</a>
        </div>
        <div class="card">
            <h3>📈 Exam Results</h3>
            <p>Check your latest marks, semester report cards, and GPA score.</p>
            <a href="../marks/view_marks.php">View Results</a>
        </div>
        <div class="card">
            <h3>📅 Attendance Tracker</h3>
            <p>Review your academic presence records and overall tracking sheet.</p>
            <a href="../Attendence/view_attendence.php">View Attendance</a>
        </div>
        <div class="card">
            <h3>View Timeline</h3>
            <p>Review your academic presence records and overall tracking sheet.</p>
            <a href="../viewTimeline.php">View Timeline</a>
        </div>
    </div>

</body>
</html>
