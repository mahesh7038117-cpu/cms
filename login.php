<?php
include 'db.php';
session_start();

// Redirect to dashboard if the user is already logged in
if (isset($_SESSION['username'])) {
    header("Location: ./dashboards/dashboard.php");
    exit();
}

$error_msg = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        // Define tables, their primary key names, and system role names to search sequentially
        $roles_to_check = [
            ['table' => 'Admin',   'id_field' => 'admin_id',   'role_name' => 'admin'],
            ['table' => 'Teacher', 'id_field' => 'teacher_id', 'role_name' => 'teacher'],
            ['table' => 'Student', 'id_field' => 'student_id', 'role_name' => 'student']
        ];

        $user_found = false;

        foreach ($roles_to_check as $check) {
            $table = $check['table'];
            $id_field = $check['id_field'];
            $role_name = $check['role_name'];

            // Prepared statement to find the user by email
            $stmt = $GLOBALS['conn']->prepare("SELECT * FROM $table WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify the securely hashed password
                if (password_verify($password, $user['password'])) {
                    // Start secure user session parameters
                    $_SESSION['username'] = $user['name'];
                    $_SESSION['role'] = $role_name;
                    $_SESSION['user_id'] = $user[$id_field];

                    $user_found = true;
                    $stmt->close();
                    
                    // Send user directly to the unified dashboard router
                    header("Location: ./dashboards/dashboard.php");
                    exit();
                } else {
                    $error_msg = "Incorrect password. Please try again.";
                    $user_found = true;
                    break; // Stop looking through other tables since email matched
                }
            }
            $stmt->close();
        }

        if (!$user_found) {
            $error_msg = "No account found with that email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
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
        .login-container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333333;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 18px;
            position: relative;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        input:focus {
            outline: none;
            border-color: #4a90e2;
        }
        input[type="password"] {
            padding-right: 65px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
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
            background-color: #4a90e2;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #357abd;
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
        p {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666666;
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

    <div class="login-container">
        <h2>Account Login</h2>
        
        <?php if (!empty($error_msg)): ?>
            <div class="error-box"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="">
            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="button" id="toggleBtn" class="toggle-password">Show</button>
            </div>
            <button type="submit" name="login">Sign In</button>
        </form>
        
        <p>New user? <a href="./register/register_gateway.php">Register here</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggleBtn');

            // Toggle password field character visibility mask
            toggleBtn.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleBtn.textContent = 'Hide';
                } else {
                    passwordInput.type = 'password';
                    toggleBtn.textContent = 'Show';
                }
            });
        });
    </script>
</body>
</html>
