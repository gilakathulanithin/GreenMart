<?php
include 'connectdb.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: login.php");
    exit();
}
// Fetch the count of pending approvals
$sql = "SELECT COUNT(*) AS pending_count FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pending_count = $row['pending_count'];
$sql = "SELECT p.id, p.name, p.description, p.price, c.name AS category, s.name AS subcategory
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN subcategories s ON p.subcategory_id = s.id";
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
    <title>GreenMart Dashboard</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="./drop.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');


main {
    max-width: 1200px;
    margin: 30 auto;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

/* Grid container for product cards */
.product-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
    /* Ensure at least 5 cards per row */
    grid-template-columns: repeat(auto-fill, minmax(calc(100% / 5 - 16px), 1fr));
    margin-bottom: 3%;
}

/* Individual product card styling */
.product-card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: box-shadow 0.3s ease;
    padding: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.product-card img {
    width: 100%;
    height: auto;
    border-bottom: 1px solid #ddd;
}

.product-card h2 {
    font-size: 1.25rem;
    margin: 10px 0;
}

.product-card p {
    margin: 5px 0;
    color: #666;
}

.product-card .action-links {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.product-card .action-links a {
    text-decoration: none;
    color: #007bff;
    font-size: 0.875rem;
    color: green;
}

.product-card .action-links a:hover {
    text-decoration: underline;
    color: green;
}

.product-card .action-links i {
    margin-right: 5px;
}

</style>
    <header>
        <div class="header-content">
            <label for="menu-toggle" id="menu-toggle-label"><i class="fas fa-bars"></i></label>
            <input type="checkbox" id="menu-toggle" hidden>
            <h1><span>GreenMart</span> </h1>
        </div>
    </header>
    
    <aside id="sidebar">
        <br><br><br><br>
        <div class="profile">
            <!-- <img src="https://via.placeholder.com/40" alt="Profile Picture"> -->
          
            <!-- <img src="https://via.placeholder.com/40" alt="Profile Picture"> -->
            <a href="admin_dashboard.php" style="text-decoration:none;color:blue; "><span><i class="fas fa-user"></i><?php echo htmlspecialchars($_SESSION['username']); ?> </span></a>
 
        </div>
        <nav>
            <ul>
          
            <!-- <a href="user_approvals.php">User Approvals (<?php echo $pending_count; ?>)</a> -->
                <!-- <li><a href="dsh.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li> -->
             <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Add Products</a></li>
            <li><a href="get_products.php"><i class="fas fa-box-open"></i> View Products</a></li>
            <li><a href="view_customer.php"><i class="fas fa-user-friends"></i> View Customers</a></li>
            <li><a href="user_approvals.php"><i class="fas fa-user-check"></i> User Approvals (<?php echo $pending_count; ?>)</a></li>
                   <!-- Order Management Dropdown -->
        <li class="dropdown">
            <a href="order_management.php" class="dropbtn"><i class="fas fa-clipboard-list"></i> Order Management</a>
            <div class="dropdown-content">
                        <!-- <a href="pending_orders.php"><i class="fas fa-clock"></i> Pending Products</a> -->
                        <a href="delivered_orders.php"><i class="fas fa-box"></i> Delivered Orders</a>
                        <a href="shipped_orders.php"><i class="fas fa-shipping-fast"></i> Shipped Orders</a>
                        <a href="cancelled_orders.php"><i class="fas fa-times-circle"></i> Cancelled Orders</a>
                    </div>
        </li>
            <li><a href=""><i class="fas fa-info-circle"></i> Inquiries</a></li>
            <li><a href=""><i class="fas fa-percentage"></i> Discounts</a></li>
            <li><a href=""><i class="fas fa-warehouse"></i> Inventory Reports</a></li>
            <li><a href=""><i class="fas fa-key"></i> Change Password</a></li>
            <li><a href="logout.php?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

            </ul>
        </nav>
    </aside>
    <main>
        <h1>Product List</h1>
        <div class="product-container">
            <?php
            // PHP code to fetch products and images
            while ($row = $result->fetch_assoc()) {
                $product_id = $row['id'];

                // Fetch product images
                $sql_images = "SELECT image_path FROM product_images WHERE product_id = ?";
                $stmt_images = $conn->prepare($sql_images);
                $stmt_images->bind_param("i", $product_id);
                $stmt_images->execute();
                $result_images = $stmt_images->get_result();

                echo "<div class='product-card'>";
                echo "<h2>" . htmlspecialchars($row['name']) . "</h2>";
                echo "<p>Category: " . htmlspecialchars($row['category']) . "</p>";
                echo "<p>Subcategory: " . htmlspecialchars($row['subcategory']) . "</p>";
                echo "<p>Description: " . htmlspecialchars($row['description']) . "</p>";
                echo "<p>Price: ₹" . number_format($row['price'], 2) . "</p>";

                if ($result_images->num_rows > 0) {
                    while ($image = $result_images->fetch_assoc()) {
                        $image_path = 'uploads/' . basename($image['image_path']);
                        echo "<img src='" . htmlspecialchars($image_path) . "' alt='Product Image'>";
                    }
                } else {
                    echo "<p>No images available.</p>";
                }

                echo "<div class='action-links'>";
                echo "<a href='edit_product.php?id=" . urlencode($product_id) . "'><i class='fas fa-edit'></i> Edit</a>";
                echo "<a href='delete_product.php?id=" . urlencode($product_id) . "' onclick='return confirm(\"Are you sure you want to delete this product?\");'><i class='fas fa-trash'></i> Delete</a>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </main>
    <footer>
        <p>&copy; 2024 GreenMart</p>
    </footer>
    <script>
      
    </script>
</body>
</html>
