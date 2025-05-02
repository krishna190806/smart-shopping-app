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

// Get summary counts
$sql_counts = "SELECT 
    COUNT(*) as total_items,
    SUM(CASE WHEN status = 'surplus' THEN 1 ELSE 0 END) as surplus_items,
    SUM(CASE WHEN quantity <= 20 THEN 1 ELSE 0 END) as low_stock_items
FROM inventory_table";
$result_counts = $conn->query($sql_counts);
$counts = $result_counts->fetch_assoc();

// Get recent activities
$sql_recent = "SELECT * FROM inventory_table ORDER BY item_id DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);

// Get discounted items count
$sql_discounts = "SELECT COUNT(*) as discount_count FROM discounted_products";
$result_discounts = $conn->query($sql_discounts);
$discount_count = $result_discounts->fetch_assoc()['discount_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retailer Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f4f6f9;
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

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stat-card .number {
            font-size: 2rem;
            color: #3498db;
            font-weight: bold;
        }

        .recent-activity {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .recent-activity h2 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-btn {
            background: #3498db;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .action-btn:hover {
            background: #2980b9;
        }

        .status-indicator {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .status-surplus {
            background-color: #2ecc71;
            color: white;
        }

        .status-low {
            background-color: #e74c3c;
            color: white;
        }

        @media (max-width: 768px) {
            .menu {
                flex-direction: column;
                align-items: center;
            }

            .stat-card .number {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
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
        <div class="dashboard-header">
            <h1>Retailer Dashboard</h1>
            <p>Welcome to your inventory management system</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Items</h3>
                <div class="number"><?php echo $counts['total_items']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Surplus Items</h3>
                <div class="number"><?php echo $counts['surplus_items']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Low Stock Items</h3>
                <div class="number"><?php echo $counts['low_stock_items']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Items on Discount</h3>
                <div class="number"><?php echo $discount_count; ?></div>
            </div>
        </div>

        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <div class="activity-list">
                <?php
                if ($result_recent->num_rows > 0) {
                    while($row = $result_recent->fetch_assoc()) {
                        $status_class = $row['quantity'] <= 20 ? 'status-low' : 
                                     ($row['status'] == 'surplus' ? 'status-surplus' : '');
                        echo '<div class="activity-item">';
                        echo '<div>';
                        echo '<strong>' . htmlspecialchars($row['item_name']) . '</strong>';
                        echo '<span> - ' . htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['unit_type']) . '</span>';
                        echo '</div>';
                        echo '<span class="status-indicator ' . $status_class . '">' . 
                             ($row['quantity'] <= 20 ? 'Low Stock' : 
                             ($row['status'] == 'surplus' ? 'Surplus' : 'Normal')) . '</span>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="activity-item">No recent activities</div>';
                }
                ?>
            </div>
        </div>

        <div class="quick-actions">
            <a href="inventory.php" class="action-btn">
                <i class="fas fa-box"></i> Manage Inventory
            </a>
            <a href="surplus_m.php" class="action-btn">
                <i class="fas fa-chart-line"></i> View Surplus
            </a>
            <a href="expired_products.php" class="action-btn">
                <i class="fas fa-exclamation-triangle"></i> Check Expiring Items
            </a>
        </div>
    </div>

    <script>
        // Add any JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // Auto refresh dashboard every 5 minutes
            setInterval(function() {
                location.reload();
            }, 300000);
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>