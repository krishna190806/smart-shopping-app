<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodmanagment1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    die(json_encode(['success' => false, 'message' => 'Invalid input data']));
}

// Start transaction
$conn->begin_transaction();

try {
    // Get current quantity
    $stmt = $conn->prepare("SELECT quantity FROM inventory_table WHERE item_id = ?");
    $stmt->bind_param("i", $data['item_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if (!$item) {
        throw new Exception("Item not found");
    }

    if ($item['quantity'] < $data['quantity']) {
        throw new Exception("Insufficient quantity");
    }

    // Update inventory
    $new_quantity = $item['quantity'] - $data['quantity'];
    $stmt = $conn->prepare("UPDATE inventory_table SET quantity = ? WHERE item_id = ?");
    $stmt->bind_param("ii", $new_quantity, $data['item_id']);
    $stmt->execute();

    // Record donation
    $date = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO donations (item_id, ngo_id, ngo_name, quantity, donation_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $data['item_id'], $data['ngo_id'], $data['ngo_name'], $data['quantity'], $date);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Donation processed successfully']);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>