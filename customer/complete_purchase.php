<?php
session_start();

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

// Initialize response array
$response = array(
    'success' => false,
    'message' => '',
    'redirect' => ''
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $item_id = isset($_POST['item_ID']) ? (int)$_POST['item_ID'] : 0;
    $purchase_quantity = isset($_POST['purchase_quantity']) ? (int)$_POST['purchase_quantity'] : 0;

    // Validate inputs
    if ($item_id <= 0 || $purchase_quantity <= 0) {
        $response['message'] = "Invalid input data";
    } else {
        // Start transaction
        $conn->begin_transaction();

        try {
            // First, check current quantity and lock the row
            $check_sql = "SELECT quantity, item_name, price_per_unit FROM inventory_table WHERE item_ID = ? FOR UPDATE";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $item_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $item = $result->fetch_assoc();
                $current_quantity = $item['quantity'];
                
                // Check if enough quantity is available
                if ($current_quantity >= $purchase_quantity) {
                    // Calculate new quantity
                    $new_quantity = $current_quantity - $purchase_quantity;
                    
                    // Update inventory
                    $update_sql = "UPDATE inventory_table SET 
                                  quantity = ?,
                                  status = CASE 
                                      WHEN ? >= 50 THEN 'surplus'
                                      ELSE 'normal'
                                  END
                                  WHERE item_id = ?";
                    
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("iii", $new_quantity, $new_quantity, $item_id);
                    
                    if ($update_stmt->execute()) {
                        // Insert into purchase history table (if you have one)
                        $purchase_date = date('Y-m-d H:i:s');
                        $total_price = $purchase_quantity * $item['price_per_unit'];
                        
                        $history_sql = "INSERT INTO purchase_history 
                                      (item_id, item_name, quantity, price_per_unit, total_price, purchase_date) 
                                      VALUES (?, ?, ?, ?, ?, ?)";
                        
                        $history_stmt = $conn->prepare($history_sql);
                        $history_stmt->bind_param("isidds", 
                            $item_id, 
                            $item['item_name'],
                            $purchase_quantity,
                            $item['price_per_unit'],
                            $total_price,
                            $purchase_date
                        );
                        $history_stmt->execute();

                        // Commit transaction
                        $conn->commit();
                        
                        $response['success'] = true;
                        $response['message'] = "Purchase successful! Quantity updated.";
                        $response['redirect'] = "buy_products.php";
                    } else {
                        throw new Exception("Error updating inventory");
                    }
                } else {
                    throw new Exception("Not enough quantity available");
                }
            } else {
                throw new Exception("Item not found");
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $response['message'] = "Error: " . $e->getMessage();
        }
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Completion</title>
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .message {
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .success {
            background-color: #dff0d8;
            border: 1px solid #3c763d;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            border: 1px solid #a94442;
            color: #a94442;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message <?php echo $response['success'] ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($response['message']); ?>
        </div>
        
        <?php if ($response['success']): ?>
            <p>Thank you for your purchase!</p>
        <?php endif; ?>
        
        <a href="<?php echo $response['success'] ? $response['redirect'] : 'javascript:history.back()'; ?>" class="button">
            <?php echo $response['success'] ? 'Continue Shopping' : 'Try Again'; ?>
        </a>
    </div>

    <?php if ($response['success']): ?>
    <script>
        // Automatically redirect after 3 seconds on success
        setTimeout(function() {
            window.location.href = '<?php echo $response['redirect']; ?>';
        }, 3000);
    </script>
    <?php endif; ?>
</body>
</html>