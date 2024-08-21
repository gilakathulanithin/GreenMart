<?php
include 'connectdb.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST variables
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    // Validate POST variables
    if ($order_id <= 0 || $product_id <= 0) {
        die("Invalid request.");
    }

    // Prepare SQL to update the status
    $update_query = "UPDATE orders o
                     JOIN order_items oi ON o.id = oi.order_id
                     SET o.status = 'Cancelled'
                     WHERE o.id = ? AND oi.product_id = ? AND o.user_id = ? AND o.status = 'Pending'";

    $stmt = $conn->prepare($update_query);

    if ($stmt) {
        $stmt->bind_param('iii', $order_id, $product_id, $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            header("location:myorders.php");
            echo "Order item has been successfully cancelled.";
        } else {
            echo "No matching order item found or the item is not in a cancellable state.";
        }
        $stmt->close();
    } else {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
} else {
    die('Invalid request method.');
}

$conn->close();
?>
