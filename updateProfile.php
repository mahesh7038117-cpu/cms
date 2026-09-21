<?php
require_once 'db.php';
session_start();

if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
    die("Database connection failed.");
}

// Block unauthenticated visitors
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$success_msg = "";
$error_msg = "";

// 1. Fetch current profile data to populate the input fields
if ($role == 'student') {
    $fetch_query = "SELECT name, email, phone FROM Student WHERE student_id = ?";
} elseif ($role == 'teacher') {
    $fetch_query = "SELECT name, email, phone FROM Teacher WHERE teacher_id = ?";
} else {
    $fetch_query = "SELECT name, email, phone FROM Admin WHERE admin_id = ?";
}

$stmt = $conn->prepare($fetch_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Process form submission data safely
if (isset($_POST['update'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $new_password = trim($_POST['password']);

    // Check if the user wants to update their password
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $error_msg = "Password must be at least 6 characters long.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            if ($role == 'student') {
                $update_query = "UPDATE Student SET name = ?, email = ?, phone = ?, password = ? WHERE student_id = ?";
            } elseif ($role == 'teacher') {
                $update_query = "UPDATE Teacher SET name = ?, email = ?, phone = ?, password = ? WHERE teacher_id = ?";
            } else {
                $update_query = "UPDATE Admin SET name = ?, email = ?, phone = ?, password = ? WHERE admin_id = ?";
            }
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssi", $name, $email, $phone, $hashed_password, $user_id);
        }
    } else {
        // Run code update without touching password column field strings
        if ($role == 'student') {
            $update_query = "UPDATE Student SET name = ?, email = ?, phone = ? WHERE student_id = ?";
        } elseif ($role == 'teacher') {
            $update_query = "UPDATE Teacher SET name = ?, email = ?, phone = ? WHERE teacher_id = ?";
        } else {
            $update_query = "UPDATE Admin SET name = ?, email = ?, phone = ? WHERE admin_id = ?";
        }
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
    }

    // Execute the database execution update safely
    if (empty($error_msg)) {
        if ($stmt->execute()) {
            $_SESSION['username'] = $name; // Keep session username aligned
            $success_msg = "Profile updated successfully!";
            
            // Refresh variables to display newly updated records instantly
            $user['name'] = $name;
            $user['email'] = $email;
            $user['phone'] = $phone;
        } else {
            $error_msg = "Error while updating records: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 18px;
            position: relative;
        }
        label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            color: #666;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            background: #fafafa;
        }
        input:focus {
            outline: none;
            border-color: #4a90e2;
            background: #fff;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 35px;
            background: none;
            border: none;
            color: #4a90e2;
            font-size: 12px;
            cursor: pointer;
            font-weight: bold;
        }
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        button[type="submit"]:hover {
            background-color: #218838;
        }
        .error-box {
            background-color: #ffeef0;
            color: #d9383a;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #d9383a;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .success-box {
            background-color: #eefbf3;
            color: #1e7e34;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #28a745;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }
        .links-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 14px;
        }
        a {
            color: #4a90e2;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="profile-container">
        <h2>✏️ Update Profile</h2>
        
        <!-- Action Alerts Response Panels -->
        <?php if (!empty($error_msg)): ?>
            <div class="error-box"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_msg)): ?>
            <div class="success-box"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <form id="updateForm" method="POST" action="">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>
            <div class="form-group">
                <label>New Password (Leave blank to keep current)</label>
                <input type="password" id="password" name="password" placeholder="min 6 characters">
                <button type="button" id="toggleBtn" class="toggle-password">Show</button>
            </div>
            
            <button type="submit" name="update">Save Changes</button>
        </form>

        <div class="links-container">
            <a href="viewProfile.php"> View Profile</a>
            <a href="dashboards/dashboard.php"> Dashboard</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggleBtn');
            const updateForm = document.getElementById('updateForm');
            const phoneInput = document.getElementById('phone');

            // Password field tracking type mask visibility options switcher
            toggleBtn.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleBtn.textContent = 'Hide';
                } else {
                    passwordInput.type = 'password';
                    toggleBtn.textContent = 'Show';
                }
            });

            // Clean sanitization parameters checking framework validation logic
            updateForm.addEventListener('submit', function(event) {
                const phoneValue = phoneInput.value.trim();
                if (!/^\d+$/.test(phoneValue)) {
                    event.preventDefault();
                    alert("Phone number must contain only numbers.");
                }
            });
        });
    </script>
</body>
</html>
