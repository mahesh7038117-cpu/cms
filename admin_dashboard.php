<?php
include '../db.php';
//session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>


    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .nav { background: #dc3545; padding: 15px; color: white; display: flex; 
            justify-content: space-between; border-radius: 5px; }
        .nav a { color: #fff; text-decoration: none; font-weight: bold; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card h3 { color: #dc3545; border-bottom: 2px solid #dc3545; padding-bottom: 5px; }
    </style>
</head>
<body>

    <div class="nav">
        <a href="../viewProfile.php" style="margin-right: 15px; color: white;">My Profile</a>
        <span>🛠️ Welcome to Administrator Core : <marquee behavior="alternate"><?php
         echo htmlspecialchars($_SESSION['username']); ?></marquee></span>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="grid">
        <div class="card">
            <h3>👥 Manage Faculty</h3>
            <p>Register new profiles, assign departments, and track teacher identity keys.</p>
            <a href="../manageTeachers.php">Teacher Management</a>
        </div>
        <div class="card">
            <h3>🎒 Enroll Students</h3>
            <p>Review admissions, delete stale indexes, and update address properties.</p>
            <a href="../manageStudents.php">Student Database</a>
        </div>
        <div class="card">
            <h3>🏢 Courses & Sections</h3>
            <p>Construct new classes, map core subjects, and allocate teaching schedules.</p>
            <a href="../configSetting.php">Configure Architecture</a>
        </div>
        <div class="card">
            <h3>Schedule Timeline</h3>
            <p>Review your academic presence records and overall tracking sheet.</p>
            <a href="../scheduleTimeline.php">Schedule Event</a>
        </div>
               <!-- Inside dashboard_teacher.php grid section -->
<div class="card">
    <h3>✍️ Attendance Records</h3>
    <p>Mark or modify daily student attendance checklists.</p>
    <a href="../Attendence/enter_attendence.php">Mark Attendance</a> 
    <a href="../Attendence/update_attendence.php" style="color: #28a745;">Review/Update</a>
</div>

<div class="card">
    <h3>📈 Academic Grading</h3>
    <p>Record exam performance logs or update submitted grades.</p>
    <a href="../marks/enter_marks.php">Enter Grades</a> 
    <a href="../marks/update_marks.php" style="color: #28a745;">Review/Update</a>
</div>
    </div>

</body>
</html>
