<?php
session_start();

// Block unauthenticated access
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$role = $_SESSION['role'];

// Route dynamically based on session role
if ($role == 'student') {
    include 'student_dashboard.php';
} elseif ($role == 'teacher') {
    include 'teacher_dashboard.php';
} elseif ($role == 'admin') {
    include 'admin_dashboard.php';
} else {
    echo "Unauthorized role access.";
}
?>
