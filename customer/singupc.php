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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone_no = $_POST['phone_no'];

    $sql = "INSERT INTO customer(fullname, email, username, password, phone_no) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fullname, $email, $username, $password, $phone_no);

    if ($stmt->execute()) {
        //echo "Registration successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
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
    <div class="container">
      
    <div>
        <h2>Sign Up Form</h2>
        <form action="loginpage.php" method="POST">

            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" placeholder="Enter Youe Name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email"  placeholder="Enter Your Email" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter Youe Username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter Youe Password" required>

            <label for="phone_no">Phone Number:</label>
            <input type="text" id="phone_no" name="phone_no" placeholder="Enter Youe Phone Number" required>

            <button type="submit" class="btn" name="submit">Sign Up</button></br>
           
            <p>You have already an account? <a href="loginpage.php">Login</a></p>
        </form>
        </div>
    </div>
    


</body>

</html>
