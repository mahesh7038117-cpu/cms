<?php
include '../db.php';

if (!isset($conn) || !$conn) {
    $conn = mysqli_connect('localhost', 'root', '', 'project');
    if (!$conn) {
        die('Database connection failed: ' . mysqli_connect_error());
    }
}

$success_msg = ""; $error_msg = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $address = !empty($_POST['address']) ? trim($_POST['address']) : null;

    $check = $conn->prepare("SELECT email FROM Student WHERE email = ?");
    $check->bind_param("s", $email); $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error_msg = "This email is already registered as a student.";
    } else {
        $stmt = $conn->prepare("INSERT INTO Student (name, email, phone, password, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $password, $address);
        if ($stmt->execute()) {
            $success_msg = "Student registered successfully! <a href='../login.php'>Login here</a>";
        } else {
            $error_msg = "Registration failed.";
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
    <title>Student Registration</title>
    <style>
        /* Shared CSS template standard rules matching typography visual anchors */
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        body { background: linear-gradient(135deg, #31e4cc, #4a90e2); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { background: #ffeef0; color: #d9383a; padding: 10px; border-radius: 4px; border-left: 4px solid #d9383a; margin-bottom: 15px; font-size: 14px; }
        .success { background: #eefbf3; color: #1e7e34; padding: 10px; border-radius: 4px; border-left: 4px solid #28a745; margin-bottom: 15px; font-size: 14px; text-align: center; }
        p { text-align: center; margin-top: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Student Sign Up</h2>
        <?php if(!empty($error_msg)) echo "<div class='error'>$error_msg</div>"; ?>
        <?php if(!empty($success_msg)) echo "<div class='success'>$success_msg</div>"; ?>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="phone" placeholder="Phone Number" required pattern="\d+">
            <input type="text" name="address" placeholder="Home Physical Address">
            <input type="password" name="password" placeholder="Password (Min 6 chars)" minlength="6" required>
            <button type="submit" name="register">Register Account</button>
        </form>
        <p>Looking for another option? <a href="../register/register_gateway.php">Back to Options</a></p>
    </div>
</body>
</html>
