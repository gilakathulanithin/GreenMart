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
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connectdb.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT id, firstname, lastname, username, email, mobile, country, state, city, address, created_at, status 
            FROM users 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
    } else {
        echo "Customer not found.";
        exit;
    }


 
} else {
    echo "Invalid request.";
    exit;
}
$sql = "SELECT COUNT(*) AS pending_count FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pending_count = $row['pending_count'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
    <style>


h1 {
    text-align: center;
    margin-bottom: 20px;
}

.card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.card-header {
    background-color:green;
    color: #fff;
    padding: 15px;
    border-radius: 8px 8px 0 0;
    text-align: center;
}

.card-body {
    padding: 15px;
}

.card-body p {
    margin: 10px 0;
    font-size: 16px;
    display: flex;
    align-items: center;
}

.card-body i {
    color: green;
    margin-right: 10px;
}

.card-body strong {
    color: #333;
}
</style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>GreenMart Dashboard</title>
    <link rel="stylesheet" href="./drop.css" >


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
            <li><a href=""><i class="fas fa-key"></i> Change Password</a></li>
            <li><a href="logout.php?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

            </ul>
        </nav>
    </aside>
    <main>
    

    <h1>Customer Details</h1>

<?php if (isset($customer)): ?>
    <div class="card">
        <div class="card-header">
            <h2>Customer Information</h2>
        </div>
        <div class="card-body">
            <p><i class="fas fa-user"></i> <strong>Full Name:</strong> <?php echo htmlspecialchars($customer['firstname'] . ' ' . $customer['lastname']); ?></p>
            <p><i class="fas fa-user-tag"></i> <strong>Username:</strong> <?php echo htmlspecialchars($customer['username']); ?></p>
            <p><i class="fas fa-envelope"></i> <strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
            <p><i class="fas fa-phone"></i> <strong>Mobile:</strong> <?php echo htmlspecialchars($customer['mobile']); ?></p>
            <p><i class="fas fa-globe"></i> <strong>Country:</strong> <?php echo htmlspecialchars($customer['country']); ?></p>
            <p><i class="fas fa-map-marker-alt"></i> <strong>State:</strong> <?php echo htmlspecialchars($customer['state']); ?></p>
            <p><i class="fas fa-city"></i> <strong>City:</strong> <?php echo htmlspecialchars($customer['city']); ?></p>
            <p><i class="fas fa-address-card"></i> <strong>Address:</strong> <?php echo htmlspecialchars($customer['address']); ?></p>
            <p><i class="fas fa-calendar-alt"></i> <strong>Created At:</strong> <?php echo htmlspecialchars($customer['created_at']); ?></p>
            <p><i class="fas fa-info-circle"></i> <strong>Status:</strong> <?php echo htmlspecialchars($customer['status']); ?></p>
          <center> <a href="view_customer.php" style="text-decoration:underline; color:green; font-weight:bolder;"> back to the customer</a></center>
        </div>
    </div>
<?php endif; ?>
    </main>
    
    <footer>
        <p>&copy; 2024 GreenMart</p>
    </footer>
    <script>





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
