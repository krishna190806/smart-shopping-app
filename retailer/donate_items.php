<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodmanagment1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item details if item_id is provided
if(isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];
    $sql = "SELECT * FROM inventory_table WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Items</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .ngo-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .ngo-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background: white;
        }
        .donation-form {
            margin-bottom: 20px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .loading {
            text-align: center;
            padding: 20px;
        }
        .error {
            color: red;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 4px;
            margin: 10px 0;
        }
        .donate-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .donate-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Donate Items to NGOs</h2>

        <?php if(isset($item)): ?>
        <div class="donation-form">
            <h3>Item Details</h3>
            <p><strong>Item Name:</strong> <?php echo htmlspecialchars($item['item_name']); ?></p>
            <p><strong>Quantity:</strong> <?php echo htmlspecialchars($item['quantity']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
            
            <div class="form-group">
                <label for="donate-quantity">Donation Quantity:</label>
                <input type="number" id="donate-quantity" min="1" max="<?php echo $item['quantity']; ?>" value="1">
            </div>
        </div>
        <?php endif; ?>

        <button onclick="getLocation()" class="donate-btn">Find Nearby NGOs</button>
        <div id="loading" class="loading" style="display: none;">
            <i class="fas fa-spinner fa-spin"></i> Finding nearby NGOs...
        </div>
        <div id="error" class="error" style="display: none;"></div>
        <div id="ngo-list" class="ngo-list"></div>
    </div>

    <script>
    function getLocation() {
        document.getElementById('loading').style.display = 'block';
        document.getElementById('error').style.display = 'none';
        document.getElementById('ngo-list').innerHTML = '';

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(findNearbyNGOs, showError);
        } else {
            showError("Geolocation is not supported by this browser.");
        }
    }

    function findNearbyNGOs(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        
        // Replace with your actual API key
        const apiKey = 'YOUR_GOOGLE_PLACES_API_KEY';
        const radius = 5000; // 5km radius
        
        fetch(`https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=${lat},${lng}&radius=${radius}&type=nonprofit_organization&key=${apiKey}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                displayNGOs(data.results);
            })
            .catch(error => {
                showError("Failed to fetch NGO data: " + error.message);
            });
    }

    function displayNGOs(ngos) {
        const container = document.getElementById('ngo-list');
        container.innerHTML = '';

        if (!ngos || ngos.length === 0) {
            container.innerHTML = '<p>No NGOs found nearby. Please try a different location.</p>';
            return;
        }

        ngos.forEach(ngo => {
            const card = document.createElement('div');
            card.className = 'ngo-card';
            card.innerHTML = `
                <h3>${ngo.name}</h3>
                <p><strong>Address:</strong> ${ngo.vicinity}</p>
                <p><strong>Rating:</strong> ${ngo.rating || 'N/A'}</p>
                <button onclick="confirmDonation('${ngo.place_id}', '${ngo.name}')" class="donate-btn">
                    Donate to this NGO
                </button>
            `;
            container.appendChild(card);
        });
    }

    function confirmDonation(ngoId, ngoName) {
        const quantity = document.getElementById('donate-quantity').value;
        const itemId = <?php echo isset($item) ? $item['item_id'] : 'null'; ?>;
        
        if (confirm(`Are you sure you want to donate ${quantity} items to ${ngoName}?`)) {
            // Process donation
            processDonation(itemId, ngoId, quantity, ngoName);
        }
    }

    function processDonation(itemId, ngoId, quantity, ngoName) {
        fetch('process_donation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                item_id: itemId,
                ngo_id: ngoId,
                quantity: quantity,
                ngo_name: ngoName
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Donation processed successfully!');
                window.location.href = 'inventory.php';
            } else {
                showError(data.message || 'Failed to process donation');
            }
        })
        .catch(error => {
            showError('Error processing donation: ' + error.message);
        });
    }

    function showError(message) {
        const errorDiv = document.getElementById('error');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        document.getElementById('loading').style.display = 'none';
    }
    </script>
</body>
</html>