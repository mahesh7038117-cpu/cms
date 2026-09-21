<?php
// Declare the connection variable before loading the database configuration so
// static analysis and the rest of this file can resolve the shared connection.
$conn = null;
include 'db.php';
if (!$conn instanceof mysqli) {
    die('Database connection is unavailable.');
}
session_start();

// Enforce strict Admin-only security access
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// 1. HANDLE NEW STUDENT ENROLLMENT (INSERT)
if (isset($_POST['enroll_student'])) {
    $name = trim($_POST['new_name']);
    $email = trim($_POST['new_email']);
    $phone = trim($_POST['new_phone']);
    // Generate a default temporary secure password (e.g., student123)
    $password = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);

    // Verify if the email is already registered to avoid SQL errors
    $check_stmt = $conn->prepare("SELECT email FROM Student WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "<div class='alert error'>Error: A student with this email is already registered!</div>";
    } else {
        // Safe insert using Prepared Statements
        $stmt = $conn->prepare("INSERT INTO Student (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $password);
        
        if ($stmt->execute()) {
            $message = "<div class='alert success'>New student enrolled successfully! Default password configured.</div>";
        } else {
            $message = "<div class='alert error'>System registry failure: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// 2. HANDLE ACCOUNT PROFILE UPDATE
if (isset($_POST['update_student'])) {
    $student_id = intval($_POST['student_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $stmt = $conn->prepare("UPDATE Student SET name = ?, email = ?, phone = ? WHERE student_id = ?");
    $stmt->bind_param("sssi", $name, $email, $phone, $student_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert success'>Student data updated successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error updating records: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// 3. HANDLE ACCOUNT DELETION
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $stmt = $conn->prepare("DELETE FROM Student WHERE student_id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert success'>Student account un-enrolled successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error removing student profile.</div>";
    }
    $stmt->close();
}

// 4. FETCH ACTIVE ENROLLMENTS
$query = "SELECT student_id, name, email, phone FROM Student ORDER BY student_id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Student Database</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background: linear-gradient(135deg, #31e4cc, #4a90e2); padding: 40px 20px; min-height: 100vh; }
        .panel-container { max-width: 950px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; margin-bottom: 20px; text-align: center; }
        h3 { color: #34495e; margin-bottom: 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 5px; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; text-align: center; font-weight: bold; }
        .success { background: #eefbf3; color: #1e7e34; border-left: 4px solid #28a745; }
        .error { background: #ffeef0; color: #d9383a; border-left: 4px solid #d9383a; }
        
        /* New Enrollment Form Section */
        .enrollment-box { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 6px; margin-bottom: 30px; }
        .form-row { display: flex; flex-wrap: wrap; gap: 15px; }
        .form-row .form-group { flex: 1; min-width: 180px; }
        .form-group label { display: block; font-size: 12px; font-weight: bold; color: #555; margin-bottom: 5px; text-transform: uppercase; }
        .enrollment-box input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .btn-enroll { background-color: #4a90e2; color: white; font-size: 14px; padding: 10px 20px; margin-top: 20px; cursor: pointer; border: none; border-radius: 4px; font-weight: bold; width: 100%; }
        .btn-enroll:hover { background-color: #357abd; }

        /* Table Layout formatting */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 25px; }
        th, td { border: 1px solid #cbd5e1; padding: 12px; text-align: left; font-size: 14px; }
        th { background: #4a90e2; color: white; }
        tr:nth-child(even) { background-color: #f8fafc; }
        
        /* Inline Row Update Design */
        .inline-form input { padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; width: 100%; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px; text-decoration: none; }
        .btn-update { background-color: #28a745; color: white; }
        .btn-update:hover { background-color: #218838; }
        .btn-delete { background-color: #dc3545; color: white; margin-left: 5px; }
        .btn-delete:hover { background-color: #c82333; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #4a90e2; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

    <div class="panel-container">
        <h2>🎒 Student Directory Controls</h2>
        <?php echo $message; ?>

        <!-- SECTION 1: ENROLL NEW STUDENT FORM -->
        <div class="enrollment-box">
            <h3>➕ Enroll New Student</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="new_name" placeholder="Enter student's full name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="new_email" placeholder="Enter student's email address" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="new_phone" placeholder="Enter student's phone number" required pattern="\d+">
                    </div>
                    <div class="form-group">
                        <label>Temporary Password</label>
                        <input type="password" name="new_password" placeholder="Min 6 characters" required minlength="6">
                    </div>
                </div>
                <button type="submit" name="enroll_student" class="btn-enroll">Confirm Enrollment Registration</button>
            </form>
        </div>

        <!-- SECTION 2: EDIT AND VIEW ACTIVE ROSTER -->
        <h3>📋 Active Student Registry Logs</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email Address</th>
                <th>Phone Number</th>
                <th>Actions</th>
            </tr>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <form method="POST" action="">
                            <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                            <td><?php echo $row['student_id']; ?></td>
                            <td>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                            </td>
                            <td>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                            </td>
                            <td>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required pattern="\d+">
                            </td>
                            <td>
                                <button type="submit" name="update_student" class="btn btn-update">Update</button>
                                <a href="admin_manage_students.php?delete_id=<?php echo $row['student_id']; ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to permanently un-enroll this student account?');">
                                   Remove
                                </a>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #777;">No active student profiles registered.</td>
                </tr>
            <?php endif; ?>
        </table>

        <a href="dashboards/dashboard.php" class="back-link">⬅️ Back to Control Dashboard</a>
    </div>

</body>
</html>
