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

// Fetch data from the inventory_table table
$sql = "SELECT * FROM inventory_table";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Table</title>
   <link rel="stylesheet" href="fatchdataInventoryTable.css">
</head>
<body>
    <h1>Inventory Table</h1>

    <table>
        <thead>
            <tr>
                <th>Item ID</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Unit Type</th>
                <th>Expiry Date</th>
                <th>Status</th>
                <th>Price/Unit (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Calculate the number of days until the expiry date
                    $expiry_date = new DateTime($row['expiry_date']);
                    $current_date = new DateTime();
                    $interval = $current_date->diff($expiry_date);
                    $days_until_expiry = $interval->days;

                    // Determine the status based on the number of days until expiry
                    if ($days_until_expiry <= 5) {
                        $status = "Near Expiry";
                    } else {
                        $status = "Fresh";
                    }

                    // Handle potential null values for Unit Type and Expiry Date
                  $unitType = $row['unit_type'] ?? ''; 
                    $expiryDate = $row['expiry_date'] ?? ''; 

                    echo "<tr>";
                    echo "<td>" . $row['item_ID'] . "</td>";
                    echo "<td>" . $row['item_name'] . "</td>";
                    echo "<td>" . $row['category'] . "</td>";
                    echo "<td>" . $row['quantity'] . "</td>";
                    echo "<td>" . $row['unit_type'] . "</td>"; // Handle null value
                    echo "<td>" . $row['expiry_date'] . "</td>"; // Handle null value
                    echo "<td>" . $status . "</td>";
                    echo "<td>" . $row['price_per_unit'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>