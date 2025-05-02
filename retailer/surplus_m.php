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

// Handle donation form submission
if(isset($_POST['donate_item'])) {
    $item_id = $_POST['item_id'];
    $donation_quantity = $_POST['donation_quantity'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, get the current item details
        $check_sql = "SELECT quantity, item_name FROM inventory_table WHERE item_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $item_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $item = $result->fetch_assoc();
        
        if($item && $item['quantity'] >= $donation_quantity) {
            // Calculate remaining quantity
            $remaining_quantity = $item['quantity'] - $donation_quantity;
            
            if($remaining_quantity == 0) {
                // Delete the item if no quantity remains
                $delete_sql = "DELETE FROM inventory_table WHERE item_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $item_id);
                $delete_stmt->execute();
            } else {
                // Update the quantity if some remains
                $update_sql = "UPDATE inventory_table SET 
                             quantity = ?,
                             status = CASE 
                                 WHEN ? >= 50 THEN 'surplus'
                                 ELSE 'normal'
                             END
                             WHERE item_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("iii", $remaining_quantity, $remaining_quantity, $item_id);
                $update_stmt->execute();
            }
            
            // Record the donation
            $donation_date = date('Y-m-d H:i:s');
            $donation_sql = "INSERT INTO donations (item_id, item_name, quantity, donation_date) 
                           VALUES (?, ?, ?, ?)";
            $donation_stmt = $conn->prepare($donation_sql);
            $donation_stmt->bind_param("isis", $item_id, $item['item_name'], $donation_quantity, $donation_date);
            $donation_stmt->execute();
            
            // Commit the transaction
            $conn->commit();
            
            echo "<script>
                    alert('Donation processed successfully!');
                    window.location.reload();
                  </script>";
        } else {
            throw new Exception("Invalid quantity or item not found");
        }
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

// Query to get surplus items
$sql = "SELECT * FROM inventory_table WHERE quantity >= 50 ORDER BY quantity DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surplus Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="surplus_m.css">
</head>
<body>
    <!-- Your existing navbar code -->
    <div class="navbar">
        <div class="menu">
            <a href="retailer.php"><i class="fas fa-home"></i> Home</a>
            <a href="inventory.php"><i class="fas fa-box"></i> Inventory Dashboard</a>
            <a href="surplus_m.php"><i class="fas fa-chart-line"></i> Surplus Management</a>
            <a href="expired_products.php"><i class="fas fa-exclamation-triangle"></i> Near-Expiry Products</a>
            <a href="aboutus.html"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="../main.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="container">
   
        <div class="header-section">
            <h2>Surplus Items (Quantity > 50)</h2>
            <!-- <button onclick="refreshPage()" class="refresh-btn">
                <i class="fas fa-sync"></i> Refresh
            </button> -->
        </div>

        <div class="table-container">
            <table id="surplusTable">
                <thead>
                    <tr>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Unit Type</th>
                        <th>Price (₹)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr id='row-{$row['item_ID']}'>";
            echo "<td>" . htmlspecialchars($row['item_ID']) . "</td>";
            echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['category']) . "</td>";
            echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
            echo "<td>" . htmlspecialchars($row['unit_type']) . "</td>";
            echo "<td>₹" . htmlspecialchars($row['price_per_unit']) . "</td>";
            echo "<td><span class='status-badge status-" . strtolower($row['status']) . "'>" 
                 . htmlspecialchars($row['status']) . "</span></td>";
            echo "<td>
                    <button onclick='showDonateModal({$row['item_ID']}, {$row['quantity']})' class='donate-btn'>
                        <i class='fas fa-hand-holding-heart'></i> Donate
                    </button>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='8' class='text-center'>No surplus items found</td></tr>";
    }
    ?>
</tbody>
            </table>
        </div>
    </div>

    <!-- Donation Modal -->
    <div id="donationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeDonateModal()">&times;</span>
        <h2>Donate Item</h2>
        <form id="donationForm" method="POST" onsubmit="return validateDonation()">
            <input type="hidden" id="item_id" name="item_id">
            <div class="form-group">
                <label for="donation_quantity">Donation Quantity:</label>
                <input type="number" id="donation_quantity" name="donation_quantity" required>
                <small id="quantity_help" class="help-text">Maximum available: <span id="max_quantity"></span></small>
            </div>
            <button type="submit" name="donate_item" class="donate-btn">
                <i class="fas fa-heart"></i> Confirm Donation
            </button>
        </form>
    </div>
</div>

<script>
    let currentItemId = null;
    let currentMaxQuantity = null;

    function showDonateModal(itemId, maxQuantity) {
        // Set current values
        currentItemId = itemId;
        currentMaxQuantity = maxQuantity;
        
        // Get modal elements
        const modal = document.getElementById('donationModal');
        const itemIdInput = document.getElementById('item_id');
        const quantityInput = document.getElementById('donation_quantity');
        const maxQuantitySpan = document.getElementById('max_quantity');
        
        // Set form values
        itemIdInput.value = itemId;
        quantityInput.max = maxQuantity;
        maxQuantitySpan.textContent = maxQuantity;
        quantityInput.value = '';
        
        // Show modal
        modal.style.display = 'block';
    }

    function closeDonateModal() {
        const modal = document.getElementById('donationModal');
        modal.style.display = 'none';
        
        // Clear form values
        document.getElementById('donation_quantity').value = '';
        currentItemId = null;
        currentMaxQuantity = null;
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('donationModal');
        if (event.target === modal) {
            closeDonateModal();
        }
    }

    function validateDonation() {
        const quantity = parseInt(document.getElementById('donation_quantity').value);
        
        if(quantity <= 0) {
            alert('Please enter a valid quantity');
            return false;
        }
        
        if(quantity > currentMaxQuantity) {
            alert('Donation quantity cannot exceed available quantity');
            return false;
        }

        if(quantity === currentMaxQuantity) {
            if(!confirm('This will remove the item from inventory. Continue?')) {
                return false;
            }
        }
        
        return true;
    }

    // Add escape key listener to close modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeDonateModal();
        }
    });
</script>

    <style>
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(-100%);
        }
    }
    </style>
</body>
</html>

<?php
$conn->close();
?>


