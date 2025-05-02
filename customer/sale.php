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

// Query to get discounted products by joining with inventory_table
$sql = "SELECT i.*, d.discount_percentage,
        (i.price_per_unit - (i.price_per_unit * d.discount_percentage / 100)) as discounted_price
        FROM inventory_table i 
        INNER JOIN discounted_products d ON i.item_ID = d.item_ID 
        WHERE d.discount_percentage > 0";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff4444;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .product-info {
            margin: 15px 0;
        }
        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9em;
        }
        .discounted-price {
            color: #ff4444;
            font-size: 1.3em;
            font-weight: bold;
            margin: 10px 0;
        }
        .savings {
            color: #4CAF50;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        .buy-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }
        .buy-btn:hover {
            background-color: #45a049;
        }
        .navbar {
            background-color: #333;
            padding: 15px;
            margin-bottom: 20px;
        }
        .menu {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        .menu a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            margin: 5px;
            transition: background-color 0.3s ease;
        }
        .menu a:hover {
            background-color: #555;
            border-radius: 4px;
        }
        .stock-status {
            margin: 10px 0;
            padding: 5px;
            text-align: center;
            border-radius: 4px;
        }
        .in-stock {
            color: #4CAF50;
        }
        .low-stock {
            color: #ff9800;
        }
        .out-of-stock {
            color: #ff4444;
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
        <div class="header">
            <h1>Special Offers & Discounts</h1>
            <p>Get amazing deals on our products!</p>
        </div>
        
        <div class="products-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $original_price = $row['price_per_unit'];
                    $discount_percentage = $row['discount_percentage'];
                    $discounted_price = $row['discounted_price'];
                    $savings = $original_price - $discounted_price;
                    
                    // Determine stock status
                    $stock_status = '';
                    $stock_class = '';
                    if ($row['quantity'] > 20) {
                        $stock_status = 'In Stock';
                        $stock_class = 'in-stock';
                    } elseif ($row['quantity'] > 0) {
                        $stock_status = 'Low Stock';
                        $stock_class = 'low-stock';
                    } else {
                        $stock_status = 'Out of Stock';
                        $stock_class = 'out-of-stock';
                    }

                    echo '<div class="product-card">';
                    echo '<div class="discount-badge">' . $discount_percentage . '% OFF</div>';
                    echo '<h3>' . htmlspecialchars($row['item_name']) . '</h3>';
                    echo '<div class="product-info">';
                    echo '<p><strong>Category:</strong> ' . htmlspecialchars($row['category']) . '</p>';
                    echo '<p><strong>Quantity:</strong> ' . htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['unit_type']) . '</p>';
                    echo '<div class="stock-status ' . $stock_class . '">' . $stock_status . '</div>';
                    echo '<p class="original-price">Original Price: ₹' . number_format($original_price, 2) . '</p>';
                    echo '<p class="discounted-price">Sale Price: ₹' . number_format($discounted_price, 2) . '</p>';
                    echo '<p class="savings">You Save: ₹' . number_format($savings, 2) . '</p>';
                    echo '</div>';
                    
                    if ($row['quantity'] > 0) {
                        echo '<button class="buy-btn" onclick="buyProduct(' . $row['item_ID'] . ', ' . $discounted_price . ')">Buy Now</button>';
                    } else {
                        echo '<button class="buy-btn" disabled style="background-color: #ccc;">Out of Stock</button>';
                    }
                    
                    echo '</div>';
                }
            } else {
                echo '<div style="grid-column: 1/-1; text-align: center; padding: 20px;">';
                echo '<h3>No products are currently on sale.</h3>';
                echo '<p>Please check back later for new offers!</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <script>
    function buyProduct(itemId, discountedPrice) {
        if(confirm('Do you want to purchase this item?')) {
            window.location.href = 'process_purchase.php?item_id=' + itemId + '&discounted_price=' + discountedPrice;
        }
    }
    </script>
</body>
</html>

<?php
$conn->close();
?>