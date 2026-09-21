<?php
include 'db.php';
session_start();

/** @var mysqli $conn */

// Block unauthenticated visitors
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Match exactly with the fields created during registration
if ($role == 'student') {
    $query = "SELECT student_id AS id, name, email, phone FROM Student WHERE student_id = ?";
} elseif ($role == 'teacher') {
    $query = "SELECT teacher_id AS id, name, email, phone FROM Teacher WHERE teacher_id = ?";
} else {
    $query = "SELECT admin_id AS id, name, email, phone FROM Admin WHERE admin_id = ?";
}
// Execute safe prepared query statement
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #31e4cc, #4a90e2);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px 0;
        }
        .profile-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333333;
            margin-bottom: 10px;
        }
        .role-badge {
            display: block;
            text-align: center;
            padding: 6px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 25px;
        }
        .student-badge { background-color: #e3f2fd; color: #0d47a1; }
        .teacher-badge { background-color: #e8f5e9; color: #1b5e20; }
        .admin-badge { background-color: #ffeef0; color: #b71c1c; }
        
        .info-group {
            margin-bottom: 18px;
            border-bottom: 1px solid #eeeeee;
            padding-bottom: 8px;
        }
        .info-label {
            font-size: 12px;
            font-weight: bold;
            color: #888888;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 16px;
            color: #333333;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #4a90e2;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <h2>Account Profile</h2>
        
        <!-- Role Specific Styled Badge -->
        <div class="role-badge <?php echo $role; ?>-badge">
            <?php echo $role; ?> Account
            <!-- Put this inside your profile.php container element -->
   

        </div>
         <a href="updateProfile.php" style="display: block; text-align: center;
        margin-top: 15px; font-weight: bold; color: #28a745;
         text-decoration: none;"> Edit Profile Info</a><BR>

        <div class="info-group">
            <div class="info-label">System User ID</div>
            <div class="info-value"><?php echo htmlspecialchars($user['id']); ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Full Name</div>
            <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Email Address</div>
            <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Phone Number</div>
            <div class="info-value"><?php echo htmlspecialchars($user['phone']); ?></div>
        </div>

        <a href="dashboards/dashboard.php" > Back to Dashboard</a>
    </div>

</body>
</html>
