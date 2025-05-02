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

// Debug line to check what's being received
// echo "Received parameters: "; print_r($_GET);

// Check if item_id or item_ID exists in the URL
if(isset($_GET['item_id']) || isset($_GET['item_ID'])) {
    // Use whichever parameter is set
    $item_id = isset($_GET['item_id']) ? $_GET['item_id'] : $_GET['item_ID'];
    
    // Validate that item_id is a number
    if(!is_numeric($item_id)) {
        die("Invalid item ID");
    }
    
    // Get item details
    $sql = "SELECT * FROM inventory_table WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $item = $result->fetch_assoc();
    } else {
        $error = "Item not found in database.";
    }
} else {
    $error = "No item ID provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Purchase</title>
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .error {
            color: red;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid red;
            background-color: #ffe6e6;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="number"] {
            padding: 8px;
            width: 200px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Purchase Details</h2>
        
        <?php if(isset($error)): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($item)): ?>
            <form action="complete_purchase.php" method="POST">
                <input type="hidden" name="item_ID" value="<?php echo htmlspecialchars($item['item_ID']); ?>">
                
                <div class="form-group">
                    <p><strong>Item:</strong> <?php echo htmlspecialchars($item['item_name']); ?></p>
                    <p><strong>Available Quantity:</strong> <?php echo htmlspecialchars($item['quantity']); ?></p>
                    <p><strong>Price per unit:</strong> ₹<?php echo htmlspecialchars($item['price_per_unit']); ?></p>
                </div>
                
                <div class="form-group">
                    <label for="purchase_quantity">Enter Quantity:</label>
                    <input type="number" 
                           id="purchase_quantity" 
                           name="purchase_quantity" 
                           required 
                           min="1" 
                           max="<?php echo htmlspecialchars($item['quantity']); ?>"
                           onchange="calculateTotal(this.value, <?php echo htmlspecialchars($item['price_per_unit']); ?>)">
                </div>

                <div class="form-group">
                    <p><strong>Total Price: </strong><span id="totalPrice">₹0</span></p>
                </div>
                
                <button type="submit">Confirm Purchase</button>
            </form>
        <?php endif; ?>
        
        <a href="buy_products.php" class="back-link">Back to Products</a>
    </div>

    <script>
        function calculateTotal(quantity, price) {
            const total = quantity * price;
            document.getElementById('totalPrice').textContent = '₹' + total.toFixed(2);
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>