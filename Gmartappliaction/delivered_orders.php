<?php
include 'connectdb.php';
session_start();

// Check if the user is an admin and is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch delivered orders
$query = "SELECT * FROM orders WHERE status = 'delivered'";
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
    text-align: center;
    font-size: 2.5em;
    color: #4CAF50;
    font-weight: bold;
    margin-bottom: 20px;
    font-family: 'Poppins', sans-serif;
    text-transform: uppercase;
    position: relative;
    z-index: 1;
    display: inline-block;
    padding: 10px 20px;
}

.orders-title::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 4px;
    background-color: #388E3C;
    z-index: -1;
    border-radius: 4px;
}

.orders-container {
    display: grid;
    grid-template-rows: auto 1fr;
    border: 1px solid #cce7d0;
    border-radius: 8px;
    overflow: hidden;
    height: 400px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: #f5fff5;
    padding-top: 20px;
}

.orders-header,
.order-row {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    padding: 10px;
    align-items: center;
}

.orders-header {
    background-color: #4CAF50;
    color: #fff;
    font-weight: bold;
    text-transform: uppercase;
    border-bottom: 2px solid #388E3C;
}

.order-row {
    background-color: #fff;
    color: #333;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid #e0e0e0;
}

.order-row:nth-child(even) {
    background-color: #eafaf1;
}

.orders-body {
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #4CAF50 #e0e0e0;
}

.orders-body::-webkit-scrollbar {
    width: 8px;
}

.orders-body::-webkit-scrollbar-thumb {
    background-color: #4CAF50;
    border-radius: 8px;
}

.order-column {
    padding: 10px;
    text-align: left;
    border-right: 1px solid #e0e0e0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.order-column:last-child {
    border-right: none;
}

.order-row:hover {
    background-color: #d8f3dc;
}

@media (max-width: 768px) {
    .order-column {
        font-size: 0.9em;
        padding: 8px;
    }
}

@media (max-width: 480px) {
    .orders-header,
    .order-row {
        grid-template-columns: 1fr 1fr;
    }

    .order-column {
        padding: 6px;
        font-size: 0.8em;
    }
}


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
    <h1 class="orders-title">Delivered Orders</h1>
    <div class="orders-container">
        <div class="orders-header">
            <div class="order-column">Order ID</div>
            <div class="order-column">User ID</div>
            <div class="order-column">Total Amount</div>
            <div class="order-column">Status</div>
            <div class="order-column">Created At</div>
            <div class="order-column">Order Date</div>
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
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>
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
