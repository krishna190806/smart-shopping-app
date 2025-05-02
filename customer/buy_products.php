
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

// Query to get available surplus items
$sql = "SELECT * FROM inventory_table ORDER BY item_name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Surplus Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Add your CSS styles here */
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .product-card h3 {
            margin: 10px 0;
            color: #333;
        }
        .product-info {
            margin: 10px 0;
        }
        .buy-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .buy-btn:hover {
            background-color: #45a049;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .refresh-btn {
            background-color: #008CBA;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .navbar {
            background-color: #2c3e50;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .menu {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .menu a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .menu a:hover {
            background-color: #34495e;
        }
    </style>
</head>
<body>
<div class="navbar">
        <div class="menu">
            <a href="customer.html">Home</a>
           <a href="buy_products.php">Buy Products</a>
           <a href="sale.php" class="sale-link">SALE</a>
           
            <a href="../main.html">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="header-section">
            <h2>Available Surplus Products</h2>
            <!-- <button onclick="refreshPage()" class="refresh-btn">
                <i class="fas fa-sync"></i> Refresh
            </button> -->
        </div>

        <div class="products-grid">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="product-card">';
                    echo '<h3>' . htmlspecialchars($row['item_name']) . '</h3>';
                    echo '<div class="product-info">';
                    echo '<p><strong>Category:</strong> ' . htmlspecialchars($row['category']) . '</p>';
                    echo '<p><strong>Available Quantity:</strong> ' . htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['unit_type']) . '</p>';
                    echo '<p><strong>Price:</strong> ₹' . htmlspecialchars($row['price_per_unit']) . '</p>';
                    echo '<p><strong>Status:</strong> ' . htmlspecialchars($row['status']) . '</p>';
                    echo '</div>';
                        echo '<button class="buy-btn" onclick="buyProduct(' . htmlspecialchars($row['item_ID']) . ')">Buy Now</button>';
                    echo '</div>';
                }   
            } else {
                echo '<div class="no-products"><p>No surplus products available at the moment.</p></div>';
            }
            ?>
        </div>
    </div>

    <!-- <script>
    function buyProduct(itemId) {
        if(confirm('Do you want to purchase this item?')) {
            // Redirect to purchase processing page
            window.location.href = 'process_purchase.php?item_id=' + itemId;
        }
    }

    function refreshPage() {
        location.reload();
    }
    </script> -->
    
<script>
function buyProduct(itemId) {
    if(confirm('Do you want to purchase this item?')) {
        // Make sure we're using 'item_id' as the parameter name
        window.location.href = 'process_purchase.php?item_id=' + itemId;
    }
}

function refreshPage() {
    location.reload();
}
</script>
</body>
</html>

<?php
$conn->close();
?>