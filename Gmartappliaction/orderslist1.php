<?php
include 'connectdb.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch orders by status for the logged-in user
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : 'all';

$query = "
    SELECT o.id AS order_id, o.order_date, o.total_amount, o.status, o.payment_status, o.created_at,
           s.name, s.mobile_number, s.address, s.city, s.postal_code,
           oi.product_id, oi.quantity, oi.price, p.name AS product_name
    FROM orders o
    JOIN shipping_addresses s ON o.shipping_id = s.address_id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = $user_id";

if ($status != 'all') {
    $query .= " AND o.status = '$status'";
}

$query .= " ORDER BY o.created_at DESC";

$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Initialize an array to store orders and their items
$orders = array();

while ($row = $result->fetch_assoc()) {
    $order_id = $row['order_id'];
    
    if (!isset($orders[$order_id])) {
        // Initialize the order if not already set
        $orders[$order_id] = array(
            'order_date' => $row['order_date'],
            'total_amount' => $row['total_amount'],
            'status' => $row['status'],
            'payment_status' => $row['payment_status'],
            'created_at' => $row['created_at'],
            'shipping' => array(
                'name' => $row['name'],
                'mobile_number' => $row['mobile_number'],
                'address' => $row['address'],
                'city' => $row['city'],
                'postal_code' => $row['postal_code']
            ),
            'items' => array()
        );
    }
    
    // Append the item to the respective order
    $orders[$order_id]['items'][] = array(
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'quantity' => $row['quantity'],
        'price' => $row['price']
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 20px;
        }
        .filter-section {
            margin-bottom: 20px;
            padding: 10px 0;
        }
        .filter-section a {
            margin-right: 15px;
            text-decoration: none;
            padding: 8px 15px;
            background-color: #ddd;
            border-radius: 5px;
            color: #000;
        }
        .filter-section a.active {
            background-color: #007bff;
            color: #fff;
        }
        .order-card {
            background-color: #fff;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .order-header-shipping {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .order-header, .shipping-info {
            width: 48%;
        }
        .order-header p, .shipping-info p {
            margin: 0;
            padding: 5px 0;
        }
        .product-info {
            margin-top: 15px;
        }
        .product-info p {
            margin: 0;
            padding: 5px 0;
        }
        .cancel-button {
            background-color: #dc3545;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .cancel-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<h1>My Orders</h1>

<div class="filter-section">
    <a href="?status=all" class="<?php echo ($status == 'all') ? 'active' : '' ?>">All Orders</a>
    <a href="?status=Pending" class="<?php echo ($status == 'Pending') ? 'active' : '' ?>">Pending</a>
    <a href="?status=Shipped" class="<?php echo ($status == 'Shipped') ? 'active' : '' ?>">Shipped</a>
    <a href="?status=Delivered" class="<?php echo ($status == 'Delivered') ? 'active' : '' ?>">Delivered</a>
    <a href="?status=Cancelled" class="<?php echo ($status == 'Cancelled') ? 'active' : '' ?>">Cancelled</a>
</div>

<?php foreach ($orders as $order_id => $order) { ?>
    <?php foreach ($order['items'] as $item) { ?>
        <div class="order-card">
            <div class="order-header-shipping">
                <div class="order-header">
                    <p><strong>Order #<?php echo $order_id; ?></strong></p>
                    <p>Order Date: <?php echo date('d F Y', strtotime($order['order_date'])); ?></p>
                    <p>Status: <?php echo $order['status']; ?></p>
                    <p>Payment Status: <?php echo $order['payment_status']; ?></p>
                    <p>Created At: <?php echo date('d F Y', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="shipping-info">
                    <h4>Shipping Address:</h4>
                    <p><?php echo $order['shipping']['name']; ?></p>
                    <p><?php echo $order['shipping']['mobile_number']; ?></p>
                    <p><?php echo $order['shipping']['address']; ?></p>
                    <p><?php echo $order['shipping']['city']; ?> - <?php echo $order['shipping']['postal_code']; ?></p>
                </div>
            </div>
            <div class="product-info">
                <p>Product: <?php echo $item['product_name']; ?></p>
                <p>Quantity: <?php echo $item['quantity']; ?></p>
                <p>Price: ₹<?php echo number_format($item['price'], 2); ?></p>
               
                <?php if ($order['status'] === 'Pending') : ?>
                    <form action="cancel_order_item.php" method="post" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['product_id']); ?>">
                        <button type="submit" class="cancel-button" onclick="return confirm('Are you sure you want to cancel this item?');">Cancel Item</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php } ?>
<?php } ?>

</body>
</html>
