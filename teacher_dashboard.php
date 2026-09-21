<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Portal</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .nav { background: #28a745; padding: 15px; color: white; display: flex; 
            justify-content: space-between; border-radius: 5px; }
        .nav a { color: #fff; text-decoration: none; font-weight: bold; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card h3 { color: #28a745; border-bottom: 2px solid #28a745; padding-bottom: 5px; }
    </style>
</head>
<body>

    <div class="nav">
        <span>👨‍🏫 Welcome to Teacher Dashboard  <marquee behavior="alternate">
            <?php echo htmlspecialchars($_SESSION['username']); ?></marquee>
        <a href="../viewProfile.php" style="margin-right: 15px; color: white;">My Profile</a></span>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="grid">
        <div class="card">
            <h3>📤 Upload Materials</h3>
            <p>Publish syllabus topics, reference links, and assignment documents for students.</p>
            <a href="../StudyMaterials/upload_materials.php">Manage Content</a>
        </div>
        <div class="card">
            <h3>✍️ Grade Exams</h3>
            <p>Input final internal marks, check performance logs, and update final lists.</p>
            <a href="../marks/enter_marks.php">Enter Grades</a>
        </div>
        <div class="card">
            <h3>✔️ Mark Attendance</h3>
            <p>Take daily roll calls, modify holiday inputs, and track absent status logs.</p>
            <a href="../Attendence/enter_attendence.php">Open Checklist</a>
        </div>
        <div class="card">
            <h3>View Timeline</h3>
            <p>Review your academic presence records and overall tracking sheet.</p>
            <a href="../viewTimeline.php">View Timeline</a>
        </div>
 

    </div>

</body>
</html>
