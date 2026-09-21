<?php
include 'db.php';
session_start();

/** @var mysqli $conn */
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection is unavailable.');
}

// Enforce strict Admin-only security access
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// 1. HANDLE NEW TEACHER REGISTRATION
if (isset($_POST['enroll_teacher'])) {
    $name = trim($_POST['new_name']);
    $email = trim($_POST['new_email']);
    $phone = trim($_POST['new_phone']);
    $password = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);

    // Verify if the email is already registered inside the Teacher table
    $check_stmt = $conn->prepare("SELECT email FROM Teacher WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "<div class='alert error'>Error: A teacher with this email is already registered!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO Teacher (name, email, phone, password) VALUES (?, ?, ?, ?)");

        if ($stmt === false) {
            $message = "<div class='alert error'>System registry failure: " . htmlspecialchars($conn->error) . "</div>";
        } else {
            $stmt->bind_param("ssss", $name, $email, $phone, $password);

            if ($stmt->execute()) {
                $message = "<div class='alert success'>New faculty profile registered successfully!</div>";
            } else {
                $message = "<div class='alert error'>System registry failure: " . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        }
    }
    $check_stmt->close();
}

// 2. HANDLE PROFILE RECORD UPDATE
if (isset($_POST['update_teacher'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $stmt = $conn->prepare("UPDATE Teacher SET name = ?, email = ?, phone = ? WHERE teacher_id = ?");
    $stmt->bind_param("sssi", $name, $email, $phone, $teacher_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert success'>Faculty record data updated successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error updating records: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// 3. HANDLE ACCOUNT REMOVAL
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $stmt = $conn->prepare("DELETE FROM Teacher WHERE teacher_id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert success'>Teacher profile removed from system registry.</div>";
    } else {
        $message = "<div class='alert error'>Error removing faculty profile.</div>";
    }
    $stmt->close();
}

// 4. FETCH ACTIVE FACULTY MEMBERS
$query = "SELECT teacher_id, name, email, phone FROM Teacher ORDER BY teacher_id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Teachers</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background: linear-gradient(135deg, #31e4cc, #4a90e2); padding: 40px 20px; min-height: 100vh; }
        .panel-container { max-width: 950px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; margin-bottom: 20px; text-align: center; }
        h3 { color: #34495e; margin-bottom: 15px; border-bottom: 2px solid #ecf0f1; padding-bottom: 5px; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; text-align: center; font-weight: bold; }
        .success { background: #eefbf3; color: #1e7e34; border-left: 4px solid #28a745; }
        .error { background: #ffeef0; color: #d9383a; border-left: 4px solid #d9383a; }
        
        /* New Faculty Enrollment Form Box */
        .enrollment-box { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 6px; margin-bottom: 30px; }
        .form-row { display: flex; flex-wrap: wrap; gap: 15px; }
        .form-row .form-group { flex: 1; min-width: 180px; }
        .form-group label { display: block; font-size: 12px; font-weight: bold; color: #555; margin-bottom: 5px; text-transform: uppercase; }
        .enrollment-box input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .btn-enroll { background-color: #4a90e2; color: white; font-size: 14px; padding: 10px 20px; margin-top: 20px; cursor: pointer; border: none; border-radius: 4px; font-weight: bold; width: 100%; }
        .btn-enroll:hover { background-color: #357abd; }

        /* Table formatting designs */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 25px; }
        th, td { border: 1px solid #cbd5e1; padding: 12px; text-align: left; font-size: 14px; }
        th { background: #4a90e2; color: white; }
        tr:nth-child(even) { background-color: #f8fafc; }
        
        /* Inline Row Inputs Layout */
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
        <h2>👨‍🏫 Faculty Member Directory</h2>
        <?php echo $message; ?>

        <!-- SECTION 1: ADD NEW TEACHER FORM -->
        <div class="enrollment-box">
            <h3>➕ Register New Teacher</h3>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="new_name" placeholder="Enter teacher's full name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="new_email" placeholder="Enter teacher's email address" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="new_phone" placeholder="Enter teacher's phone number" required pattern="\d+">
                    </div>
                    <div class="form-group">
                        <label>Temporary Password</label>
                        <input type="password" name="new_password" placeholder="Min 6 characters" required minlength="6">
                    </div>
                </div>
                <button type="submit" name="enroll_teacher" class="btn-enroll">Register New Instructor</button>
            </form>
        </div>

        <!-- SECTION 2: EDIT AND VIEW ACTIVE ROSTER -->
        <h3>📋 Active Faculty Records</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Instructor Name</th>
                <th>Email Address</th>
                <th>Phone Number</th>
                <th>Actions</th>
            </tr>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <form method="POST" action="">
                            <input type="hidden" name="teacher_id" value="<?php echo $row['teacher_id']; ?>">
                            <td><?php echo $row['teacher_id']; ?></td>
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
                                <button type="submit" name="update_teacher" class="btn btn-update">Update</button>
                                <a href="admin_manage_teachers.php?delete_id=<?php echo $row['teacher_id']; ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to permanently delete this instructor account profile?');">
                                   Remove
                                </a>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #777;">No teacher profiles registered.</td>
                </tr>
            <?php endif; ?>
        </table>

        <a href="dashboards/dashboard.php" class="back-link">⬅️ Back to Control Dashboard</a>
    </div>

</body>
</html>
