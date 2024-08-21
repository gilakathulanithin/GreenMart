<?php
include 'connectdb.php';
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    
    $update_query = "UPDATE orders SET status = '$new_status' WHERE id = $order_id";
    if (mysqli_query($conn, $update_query)) {
        echo "<p class='success-msg'>Order status updated successfully!</p>";
    } else {
        echo "<p class='error-msg'>Error updating order status: " . mysqli_error($conn) . "</p>";
    }
}

// Fetch orders with status 'Pending'
$query = "SELECT * FROM orders WHERE status = 'Pending'";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
// Fetch the count of pending approvals
$sql = "SELECT COUNT(*) AS pending_count FROM users WHERE status = 'pending'";
$pendingResult = mysqli_query($conn, $sql);
if (!$pendingResult) {
    die("Query failed: " . mysqli_error($conn));
}
$row = mysqli_fetch_assoc($pendingResult);
$pending_count = $row['pending_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="./drop.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
           .orders-title {
    text-align: center; /* Center the text horizontally */
    font-size: 2.5em; /* Larger font size for emphasis */
    color: #4CAF50; /* Green Mart primary color */
    font-weight: bold;
    margin-bottom: 20px; /* Add space below the title */
    font-family: 'Poppins', sans-serif; /* Custom font, fallback to sans-serif */
    text-transform: uppercase; /* Uppercase for a stronger look */
    position: relative; /* Enable pseudo-element positioning */
    z-index: 1;
    display: inline-block; /* Shrink the width to content */
    padding: 10px 20px; /* Add padding around the text */
}

.orders-title::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 4px;
    background-color: #388E3C; /* Slightly darker green underline */
    z-index: -1; /* Place it behind the text */
    border-radius: 4px; /* Rounded corners for a polished look */
}

.orders-title::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -10px;
    transform: translateX(-50%);
    width: 50px;
    height: 4px;
    background-color: #c8e6c9; /* Light green accent line */
    border-radius: 2px;
}



.orders-container {
    display: grid;
    grid-template-rows: auto 1fr;
    border: 1px solid #cce7d0; /* Light green border */
    border-radius: 8px;
    overflow: hidden;
    height: 400px; /* Static height for the container */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    background-color: #f5fff5; /* Very light green background */
}

.orders-header,
.order-row {
    display: grid;
    grid-template-columns: repeat(6, 1fr); /* 6 equal columns */
    padding: 10px;
    align-items: center; /* Center content vertically */
}

.orders-header {
    background-color: #4CAF50; /* Green Mart primary color */
    color: #fff;
    font-weight: bold;
    text-transform: uppercase; /* Uppercase header for clarity */
    border-bottom: 2px solid #388E3C; /* Slightly darker bottom border */
    
}

.order-row {
    background-color: #fff;
    color: #333;
    transition: background-color 0.2s ease; /* Smooth transition for hover effect */
    border-bottom: 1px solid #e0e0e0;
}

.order-row:nth-child(even) {
    background-color: #eafaf1; /* Light green background for alternating rows */
}

.orders-body {
    overflow-y: auto; /* Enable vertical scrolling */
    scrollbar-width: thin;
    scrollbar-color: #4CAF50 #e0e0e0; /* Custom scrollbar color matching Green Mart theme */
}

.orders-body::-webkit-scrollbar {
    width: 8px;
}

.orders-body::-webkit-scrollbar-thumb {
    background-color: #4CAF50; /* Scrollbar thumb color */
    border-radius: 8px;
}

.order-column {
    padding: 10px;
    text-align: left;
    border-right: 1px solid #e0e0e0;
    white-space: nowrap; /* Prevent text wrapping */
    overflow: hidden;
    text-overflow: ellipsis; /* Truncate overflow text with ellipsis */
}

.order-column:last-child {
    border-right: none; /* Remove border from the last column */
}

.order-row:hover {
    background-color: #d8f3dc; /* Soft green highlight on hover */
}

@media (max-width: 768px) {
    .order-column {
        font-size: 0.9em; /* Slightly smaller font on smaller screens */
        padding: 8px;
    }
}

@media (max-width: 480px) {
    .orders-header,
    .order-row {
        grid-template-columns: 1fr 1fr; /* Stack columns on very small screens */
    }

    .order-column {
        padding: 6px;
        font-size: 0.8em;
    }
}
/* Dropdown Container */


    </style>
</head>
<body>
<header>
        <div class="header-content">
            <label for="menu-toggle" id="menu-toggle-label"><i class="fas fa-bars"></i></label>
            <input type="checkbox" id="menu-toggle" hidden>
            <h1><span>GreenMart</span> </h1>
        </div>
    </header>
    
    <button id="menu-toggle" class="menu-toggle">☰</button>

    <div class="dashboard">
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
                        <a href="pending_orders.php"><i class="fas fa-clock"></i> Pending Products</a>
                        <a href="delivered_orders.php"><i class="fas fa-box"></i> Delivered Orders</a>
                        <a href="shipped_orders.php"><i class="fas fa-shipping-fast"></i> Shipped Orders</a>
                        <a href="cancelled_orders.php"><i class="fas fa-times-circle"></i> Cancelled Orders</a>
                    </div>
        </li>
            <!-- <li><a href=""><i class="fas fa-info-circle"></i> Inquiries</a></li> -->
            <li><a href=""><i class="fas fa-percentage"></i> Discounts</a></li>
            <li><a href="inventory_report.php"><i class="fas fa-warehouse"></i> Inventory Reports</a></li>
            <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
            <li><a href="logout.php?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

            </ul>
        </nav>
    </aside>
    <main class="content">
        <h1 class="orders-title">Orders</h1>

        <div class="orders-container">
            <div class="orders-header">
                <div class="order-column">Order ID</div>
                <div class="order-column">User ID</div>
                <div class="order-column">Total Amount</div>
                <div class="order-column">Status</div>
                <div class="order-column">Created At</div>
                <div class="order-column">Order Date</div>
                <div class="order-column">Actions</div>
            </div>
            <div class="orders-body">
                <?php while ($order = mysqli_fetch_assoc($result)): ?>
                <div class="order-row">
                    <div class="order-column"><?php echo htmlspecialchars($order['id']); ?></div>
                    <div class="order-column"><?php echo htmlspecialchars($order['user_id']); ?></div>
                    <div class="order-column"><?php echo htmlspecialchars($order['total_amount']); ?></div>
                    <div class="order-column"><?php echo htmlspecialchars($order['status']); ?></div>
                    <div class="order-column"><?php echo htmlspecialchars($order['created_at']); ?></div>
                    <div class="order-column"><?php echo htmlspecialchars($order['order_date']); ?></div>
                    <div class="order-column">
                        <form method="POST" action="">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                            <select name="new_status" class="action-dropdown">
                                <option value="shipped" <?php if ($order['status'] == 'shipped') echo 'selected'; ?>>Mark as Shipped</option>
                                <option value="canceled" <?php if ($order['status'] == 'canceled') echo 'selected'; ?>>Mark as Canceled</option>
                            </select>
                            <button type="submit" class="action-submit-button">Update</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>


<?php
// Close the database connection
mysqli_close($conn);
?>
    </div>

    <footer class="footer">
        <p>&copy; 2024 GreenMart. All Rights Reserved.</p>
    </footer>

    <script>
        document.querySelector('.dropdown').addEventListener('click', function(event) {
    event.stopPropagation();
    this.querySelector('.dropdown-content').classList.toggle('show');
});

window.onclick = function(event) {
    if (!event.target.matches('.dropbtn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
};

        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.querySelector('.sidebar');
        
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
