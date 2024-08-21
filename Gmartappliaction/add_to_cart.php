<?php
session_start();
include 'connectdb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = intval($_POST['product_id']);
    $userId = $_SESSION['user_id']; // Ensure user ID is in the session

    // Initialize the cart in the session if not already set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // Check if the product is already in the cart
    if (isset($_SESSION['cart'][$productId])) {
        if ($_SESSION['cart'][$productId] < 6) {
            $_SESSION['cart'][$productId] += 1;
            $message = "Product quantity updated in cart!";
        } else {
            $message = "You can only add up to 6 units of this product.";
        }
    } else {
        $_SESSION['cart'][$productId] = 1;
        $message = "Product added to cart!";
    }

    // Return a JSON response
    echo json_encode([
        'success' => true,
        'message' => $message,
        'quantity' => $_SESSION['cart'][$productId]
    ]);
    exit();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit();
}
?>
