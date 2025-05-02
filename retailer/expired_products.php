<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodmanagment1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Update Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $id = $_POST["id"];
    $discount = $_POST["discount"];

    // First update the discount percentage
    $sql = "UPDATE discounted_products SET discount_percentage='$discount' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: expired_products.php"); // Redirect after update
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Fetch Data - Modified query to remove new_price calculation
$sql = "SELECT i.*, d.id as discount_id, d.discount_percentage 
        FROM inventory_table i 
        LEFT JOIN discounted_products d ON i.item_ID = d.item_id 
        WHERE i.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)";
$result = $conn->query($sql);

?>

<?php
// Database connection code...

// Modified query to avoid duplicates
$sql = "SELECT i.*, 
        COALESCE(d.discount_percentage, 0) as discount_percentage 
        FROM inventory_table i 
        LEFT JOIN discounted_products d ON i.item_id = d.item_id 
        WHERE i.status = 'near_expiry' 
        GROUP BY i.item_id";  // This will ensure one row per item

// Rest of your display code...
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discounted Nearly Expired Products</title>
    <link rel="stylesheet" href="expired_products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script src="expired_products.js"></script>
</head>

<body>
    <!-- <div class="navbar">
    <div class="menu">
        <a href="retailer.html">Home</a>
        <a href="inventory.php">Inventory Dashboard</a>
        <a href="surplus_m.html">Surplus Management</a>
        <a href="expired_products.php">Nearly Expired Products</a>
        <a href="aboutus.html">About Us</a>
        <a href="../main.html">Logout</a>
    </div>
</div> -->
<div class="navbar">
        <div class="menu">
            <a href="retailer.php"><i class="fas fa-home"></i> Home</a>
            <a href="inventory.php"><i class="fas fa-box"></i> Inventory Dashboard</a>
            <a href="surplus_m.php"><i class="fas fa-exclamation-triangle"></i> Near-Expiry Products</a>
            <a href="expired_products.php"><i class="fas fa-chart-line"></i> Surplus Management</a>
            <a href="aboutus.html"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="../main.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Discounted Nearly Expired Products</h2>
        <table>
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Unit Type</th>
                    <th>Status</th>
                    <th>Expiry Date</th>
                    <th>Original Price (₹)</th>
                    <th>Discount (%)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr id="row-<?php echo $row['discount_id']; ?>">
                        <td><?php echo $row['item_ID']; ?></td>
                        <td><?php echo $row['item_name']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo $row['unit_type']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['expiry_date']; ?></td>
                        <td>₹<?php echo $row['price_per_unit']; ?></td>
                        <td class="discount">
                            <span class="discount-value"><?php echo $row['discount_percentage']; ?>%</span>
                            <input type="number" name="discount" class="discount-input" style="display: none;" min="0"
                                max="100" value="<?php echo $row['discount_percentage']; ?>">
                        </td>
                        <td>
                            <form method="POST" class="update-form">
                                <button type="button" class="edit-btn"
                                    onclick="enableEdit(<?php echo $row['discount_id']; ?>)">Edit</button>
                                <button type="submit" class="update-btn" name="update"
                                    style="display: none;">Update</button>
                                <input type="hidden" name="id" value="<?php echo $row['discount_id']; ?>">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<?php $conn->close(); ?>
