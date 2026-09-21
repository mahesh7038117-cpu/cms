<?php
include 'db.php';

$success_msg = "";
$error_msg = "";

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hash
    $role = $_POST['role'];

    // Pick table based on role selection
    if ($role == 'teacher') {
        $query = "INSERT INTO Teacher (name, email, phone, password) VALUES
         ('$name', '$email', '$phone', '$password')";
    } elseif ($role == 'student') {
        $query = "INSERT INTO Student (name, email, phone, password) VALUES 
        ('$name', '$email', '$phone', '$password')";
    } else {
        $query = "INSERT INTO Admin (name, email, phone, password) VALUES
         ('$name', '$email', '$phone', '$password')";
    }

    if (mysqli_query($GLOBALS['conn'], $query)) {
        $success_msg = "Registration successful! <a href='login.php'>Login here</a>";
    } else {
        $error_msg = "Error: " . mysqli_error($GLOBALS['conn']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        .register-container {
            background-color: #d7b1b1;
            padding: 30px;
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
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        input[type="password"] {
            padding-right: 65px;
        }
        input:focus, select:focus {
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
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Sign Up</h2>
        
        <!-- Alerts for feedback -->
        <div id="error-message" class="error-box" style="<?php echo empty($error_msg) ?
             'display: none;' : ''; ?>">
            <?php echo $error_msg; ?>
        </div>
        
        <?php if (!empty($success_msg)): ?>
            <div class="success-box"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="">
            <div class="form-group">
                <input type="text" id="name" name="name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="text" id="phone" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="button" id="toggleBtn" class="toggle-password">Show</button>
            </div>
            <div class="form-group">
                <select name="role">
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="register">Register</button>
        </form>
        <p>Have an account? <a href="login.php">Login here</a></p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('registerForm');
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggleBtn');
            const errorBox = document.getElementById('error-message');

            // 1. Password Visibility Toggle
            toggleBtn.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleBtn.textContent = 'Hide';
                } else {
                    passwordInput.type = 'password';
                    toggleBtn.textContent = 'Show';
                }
            });

            // 2. Client-Side Validation on Submit
            registerForm.addEventListener('submit', function(event) {
                let errorMessage = "";

                const nameValue = nameInput.value.trim();
                const emailValue = emailInput.value.trim();
                const phoneValue = phoneInput.value.trim();
                const passwordValue = passwordInput.value.trim();

                // Validation rules
                if (nameValue === "" || emailValue === "" || phoneValue === "" || passwordValue === "") {
                    errorMessage = "All fields are required.";
                } else if (passwordValue.length < 6) {
                    errorMessage = "Password must be at least 6 characters long.";
                } else if (!/^\d+$/.test(phoneValue)) {
                    errorMessage = "Phone number must contain only numbers.";
                }

                // Block form submission if rules fail
                if (errorMessage !== "") {
                    event.preventDefault(); 
                    errorBox.textContent = errorMessage;
                    errorBox.style.display = 'block';
                }
            });

            // 3. Clear errors dynamically when typing
            const inputs = [nameInput, emailInput, phoneInput, passwordInput];
            inputs.forEach(input => {
                input.addEventListener('input', () => errorBox.style.display = 'none');
            });
        });
    </script>
</body>
</html>
