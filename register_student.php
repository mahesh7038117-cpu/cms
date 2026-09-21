<?php
include '../db.php';

$success_msg = "";
$error_msg = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Secure 60-char hash

    // $conn is initialized by db.php.
    /** @var mysqli $conn */
    // Verify if email already exists to prevent duplicate account database errors
    $check = $conn->prepare("SELECT email FROM Student WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error_msg = "This email address is already registered as a student.";
    } else {
        // Safe database record insertion using prepared statements
        $stmt = $conn->prepare("INSERT INTO Student (name, email, phone, address, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $address, $password);
        
        if ($stmt->execute()) {
            $success_msg = "Student registered successfully! <a href='../login.php'>Login here</a>";
        } else {
            $error_msg = "Database error: Failed to record student enrollment profile.";
        }
        $stmt->close();
    }
    $check->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
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
        .container {
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
            margin-bottom: 15px;
            position: relative;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #4a90e2;
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
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
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
        .success-box a {
            font-weight: bold;
            text-decoration: underline;
            color: #1e7e34;
        }
        p {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #666666;
        }
        a {
            color: #4a90e2;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Student Sign Up</h2>
        
        <?php if (!empty($error_msg)): ?>
            <div class="error-box"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_msg)): ?>
            <div class="success-box"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <form id="studentForm" method="POST" action="">
            <div class="form-group">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="form-group">
                <input type="text" id="phone" name="phone" placeholder="Phone Number" required pattern="\d+">
            </div>
            <div class="form-group">
                <input type="text" name="address" placeholder="Home Address" required>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password" required minlength="6">
                <button type="button" id="toggleBtn" class="toggle-password">Show</button>
            </div>
            <button type="submit" name="register">Register Student</button>
        </form>
        <p>Go back to selection gateway? <a href="register_gateway.php">Click here</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggleBtn');

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
