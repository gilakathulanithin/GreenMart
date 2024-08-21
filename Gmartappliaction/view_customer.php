<?php
session_start();  // Ensure this is at the top

include "connectdb.php";  // Include the database connection file

if (!$conn) {
    die("Database connection not established.");
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);


$sql = "SELECT id, firstname, lastname, username, email, mobile, country, state, city, address, created_at, status 
        FROM users 
        WHERE  role ='user'";
$result = $conn->query($sql);

$customers = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}
$sql = "SELECT COUNT(*) AS pending_count FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pending_count = $row['pending_count'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<style>
    body {
    font-family: Arial, sans-serif;
    margin: 20px;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}
main{
    margin-top: 10%;
    margin-bottom: 5%;
}
.container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

.card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    width: 300px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.card-header {
    background-color: #f5f5f5;
    padding: 15px;
    border-bottom: 1px solid #ddd;
}

.card-body {
    padding: 15px;
}

.card-footer {
    background-color: #f5f5f5;
    padding: 15px;
    border-top: 1px solid #ddd;
    text-align: center;
}

button {
    background-color: green;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 16px;
}


</style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>GreenMart Dashboard</title>
    <link rel="stylesheet" href="./drop.css">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-content">
            <label for="menu-toggle" id="menu-toggle-label"><i class="fas fa-bars"></i></label>
            <input type="checkbox" id="menu-toggle">
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
                        <a href="pending_orders.php"><i class="fas fa-clock"></i> Pending Products</a>
                        <a href="delivered_orders.php"><i class="fas fa-box"></i> Delivered Orders</a>
                        <a href="shipped_orders.php"><i class="fas fa-shipping-fast"></i> Shipped Orders</a>
                        <a href="cancelled_orders.php"><i class="fas fa-times-circle"></i> Cancelled Orders</a>
                    </div>
        </li>
            <!-- <li><a href=""><i class="fas fa-info-circle"></i> Inquiries</a></li> -->
            <li><a href=""><i class="fas fa-percentage"></i> Discounts</a></li>
            <li><a href=""><i class="fas fa-warehouse"></i> Inventory Reports</a></li>
            <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
            <li><a href="logout.php?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

            </ul>
        </nav>
    </aside>
    <main>
    <div class="container">
    <?php if (!empty($customers)): ?>
        <?php foreach ($customers as $customer): ?>
            <div class="card">
                <div class="card-header">
                    <h2><?php echo htmlspecialchars($customer['firstname'] . ' ' . $customer['lastname']); ?></h2>
                </div>
                <div class="card-body">
                <center>    <p style="font-weight:bolder;">Status: <?php echo htmlspecialchars($customer['status']); ?></p></center>
                </div>
                <div class="card-footer">
                    <button onclick="viewDetails(<?php echo htmlspecialchars($customer['id']); ?>)">View Details</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No customers found.</p>
    <?php endif; ?>
</div>
    </main>
    
    <footer>
        <p>&copy; 2024 GreenMart</p>
    </footer>
    <script>


function viewDetails(id) {
    window.location.href = "view_details.php?id=" + id;
}


        document.addEventListener('DOMContentLoaded', () => {
            const menuToggle = document.querySelector('#menu-toggle');
            const sidebar = document.querySelector('#sidebar');

            menuToggle.addEventListener('change', () => {
                if (menuToggle.checked) {
                    sidebar.classList.add('show');
                } else {
                    sidebar.classList.remove('show');
                }
            });

            document.querySelector('#sidebar-logout').addEventListener('click', () => {
                // Handle logout functionality
                alert('Logged out successfully!');
            });
        });
    </script>
</body>
</html>
