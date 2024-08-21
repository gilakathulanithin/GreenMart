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

// Fetch pending users
$sql_users = "SELECT id, firstname, lastname FROM users WHERE status = 'pending'";
$result_users = $conn->query($sql_users);

if (!$result_users) {
    die("Error fetching users: " . $conn->error);
}

// Fetch the count of pending users
$sql_count = "SELECT COUNT(*) AS pending_count FROM users WHERE status = 'pending'";
$result_count = $conn->query($sql_count);

if (!$result_count) {
    die("Error fetching pending count: " . $conn->error);
}

$row = $result_count->fetch_assoc();
$pending_count = $row['pending_count'];

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<style>


h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    font-size: 16px;
    color: #007bff;
    text-decoration: none;
}

.back-link:hover {
    text-decoration: underline;
}

.card-container {
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
    padding: 20px;
    max-width: 300px;
    width: 100%;
    box-sizing: border-box;
    text-align: center;
}

.card h3 {
    margin-top: 0;
    color: green;
}

.card p {
    margin: 10px 0;
}

.view-details {
    display: inline-block;
    margin-top: 10px;
    font-size: 16px;
    color: green;
    text-decoration: none;
    font-weight: bold;
}

.view-details:hover {
    text-decoration: underline;
}

.no-approvals {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin: 20px;
    text-align: center;
}

.no-approvals p {
    margin: 0;
    color: #333;
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
    <h1>User Approvals</h1>
<!-- <a href="admindashboard.php" class="back-link">Back to Dashboard</a> -->

<div class="card-container">
    <?php
    if ($result_users->num_rows > 0) {
        while ($row = $result_users->fetch_assoc()) {
            ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></h3>
                <a href="approve_user.php?user_id=<?php echo htmlspecialchars($row['id']); ?>" class="view-details">View Details</a>
            </div>
            <?php
        }
    } else {
        echo "<div class='no-approvals'><p>No pending approvals at this time.</p></div>";
    }
    ?>
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
