<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Portal Gateway</title>
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
            padding: 20px;
        }
        .gateway-container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 26px;
        }
        .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .role-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            text-decoration: none;
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .role-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .role-student { background-color: #007bff; }
        .role-student:hover { background-color: #0056b3; }
        
        .role-teacher { background-color: #28a745; }
        .role-teacher:hover { background-color: #218838; }
        
        .role-admin { background-color: #dc3545; }
        .role-admin:hover { background-color: #bd2130; }
        
        p.login-redirect {
            margin-top: 25px;
            font-size: 14px;
            color: #666666;
        }
        a.login-link {
            color: #4a90e2;
            text-decoration: none;
            font-weight: bold;
        }
        a.login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="gateway-container">
        <h2>Account Registration</h2>
        <p class="subtitle">Select your designated institution role to create an account profile</p>
        
        <!-- Role Specific Routing Gateways -->
        <a href="register_student.php" class="role-btn role-student"> Register as a Student</a>
        <a href="register_teacher.php" class="role-btn role-teacher"> Register as a Faculty Teacher</a>
        <a href="register_admin.php" class="role-btn role-admin">Register as an Administrator</a>
        
        <p class="login-redirect">Already have an account? <a href="../login.php" class="login-link">Login here</a></p>
    </div>

</body>
</html>
