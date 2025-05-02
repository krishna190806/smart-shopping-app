<?php
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "foodmanagment1"; // Change to your actual DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch inventory data
$sql = "SELECT * FROM inventory_table";
$result = $conn->query($sql);
?>



<?php
// Add this to your inventory management code

function updateItemStatus($conn, $item_id) {
    // Get item details
    $sql = "SELECT quantity, expiry_date FROM inventory_table WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    // Calculate days until expiry
    $expiry = new DateTime($item['expiry_date']);
    $today = new DateTime();
    $days_until_expiry = $today->diff($expiry)->days;

    // Determine status based on quantity and expiry
    $new_status = '';
    if ($days_until_expiry <= 3) {
        $new_status = 'near_expiry';
    } elseif ($item['quantity'] <= 10) {
        $new_status = 'low_stock';
    } elseif ($item['quantity'] >= 50) {
        $new_status = 'surplus';
    } else {
        $new_status = 'fresh';
    }

    // Update status in database
    $update_sql = "UPDATE inventory_table SET status = ? WHERE item_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ss", $new_status, $item_id);
    $update_stmt->execute();
}

// Trigger function after any inventory update
function updateAllStatuses($conn) {
    $sql = "SELECT item_id FROM inventory_table";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        updateItemStatus($conn, $row['item_id']);
    }
}

// Add this to your cron job or run daily
updateAllStatuses($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Table</title>
    <link rel="stylesheet" href="inventory.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- <div class="navbar">
        <div class="menu">
            <a href="retailer.html">Home</a>
            <a href="retailer.html">Inventory Dashboard</a>
            <a href="surplus_m.html">Surplus Management</a>
            <a href="expired_products.php">Near-Expiry Products</a>
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

    <h1  style="margin:50px;">Inventory Table</h1>

    <!-- Date Block -->
    
    <div class="date-block">
        <label for="date-picker">Select Date: </label>
        <input type="date" id="date-picker" name="date-picker" value="<?php echo date('Y-m-d'); ?>" />
        <div id="current-date">
            <strong>Current Date: </strong> <span id="date-span"></span>
        </div>
    </div>


  
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
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['item_ID']; ?></td>
                    <td><?php echo $row['item_name']; ?></td>
                    <td><?php echo $row['category']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['unit_type']; ?></td>
                    <td><?php echo $row['expiry_date']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['price_per_unit']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
            
    <button class="action-btn">
        <a href="inventory_table.php" style="color: white; text-decoration: none;">Add/Update Inventory</a>
    </button>

    <script src="inventory.js"> </script>
</body>

</html>

<?php $conn->close(); ?>