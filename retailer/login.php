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

if ($_SERVER['REQUEST_METHOD'] === 'POST'&& isset($_POST['register'])) ///
{
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone_no = $_POST['phone_no'];

    $sql = "INSERT INTO user1 (fullname, email, username, password, phone_no) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fullname, $email, $username, $password, $phone_no);

    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];



// Fetch username and password from the database
    // $sql = "SELECT username, password FROM user1 ";////
    // $result = $conn->query($sql);

    // if ($result->num_rows > 0) {
    //     // echo "<h2>Registered Users:</h2><ul>";
    // } else {
    //     echo "No users found.";
    // }


    $sql = "SELECT username, password FROM user1 WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists, now check the password
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            echo "Login successful!";
            // Redirect or set a session variable for logged-in user
            // session_start();
            // $_SESSION['username'] = $user['username'];
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "No user found with this email address.";
    }

    $stmt->close();
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
<!-- <div class="container">
        <--Login Form -->
        
            <!-- <h2>Login</h2>
            <div>
            <form action="main.html" method="POST">
                <label for="login-email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="login-password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" class="btn" name="submit">Login</button>
            </form>
            </div>
           
            <p>Don't have an account?</p> -->
         
         <div class="container">
        <!-- Login Form -->
        <div class="form-container" id="login-form">
            <h2>Login</h2>
            <form action="retailer.php" method="POST">
                <label for="login-email">Email:</label>
                <input type="email" id="login-email" placeholder="Enter your email" required>

                <label for="login-password">Password:</label>
                <input type="password" id="login-password" placeholder="Enter your password" required>

                <button type="submit" class="btn">Login</button>
            </form>
            <p>Don't have an account? <a href="sign.php" onclick="toggleForm()">Sign Up</a></p>
        </div>
    </form>
    </div>

</body>
</html>