<?php
include 'connectdb.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Delete product images
    $sql_images = "DELETE FROM product_images WHERE product_id = ?";
    $stmt_images = $conn->prepare($sql_images);
    $stmt_images->bind_param("i", $product_id);
    $stmt_images->execute();

    // Delete the product
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        header("location:get_products.php");
    } else {
        echo "Error deleting product: " . $conn->error;
    }
} else {
    die("No product ID specified.");
}
?>
