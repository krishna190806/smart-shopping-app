<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodmanagment1";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
$success_message = "";
$registration_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $phone_no = trim($_POST['phone_no']);

    // Basic validation
    if (empty($fullname) || empty($email) || empty($username) || empty($password) || empty($phone_no)) {
        $error_message = "All fields are required";
    } else {
        // Phone number validation
        if (!preg_match("/^[0-9]{10}$/", $phone_no)) {
            $error_message = "Phone number must be exactly 10 digits";
        } else {
            // Check if username, email, or phone number already exists
            $check_sql = "SELECT * FROM user1 WHERE username = ? OR email = ? OR phone_no = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("sss", $username, $email, $phone_no);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['username'] === $username) {
                    $error_message = "Username already exists";
                } elseif ($row['email'] === $email) {
                    $error_message = "Email already exists";
                } elseif ($row['phone_no'] === $phone_no) {
                    $error_message = "Phone number already exists";
                }
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $sql = "INSERT INTO user1 (fullname, email, username, password, phone_no) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $fullname, $email, $username, $hashed_password, $phone_no);

                if ($stmt->execute()) {
                    $registration_success = true;
                    $success_message = "Registration successful! Please login.";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }

                $stmt->close();
            }
            $check_stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <div>
            <h2>Sign Up</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="sign.php" method="POST">
                <label for="fullname">Full Name:</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter Your Name" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter Your Email" required>

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Enter Your Username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter Your Password" required>

                <label for="phone_no">Phone Number:</label>
                <input type="tel" id="phone_no" name="phone_no" 
                       placeholder="Enter 10-digit Phone Number" 
                       pattern="[0-9]{10}" 
                       title="Please enter exactly 10 digits"
                       maxlength="10"
                       required>

                <button type="submit" class="btn" name="submit">Sign Up</button>
                <p>Already have an account? <a href="login.php">Login</a></p>
            </form>
        </div>
    </div>

    <?php if ($registration_success): ?>
    <script>
        alert("Registration successful! Please login.");
        window.location.href = "login.php";
    </script>
    <?php endif; ?>
</body>

</html>
