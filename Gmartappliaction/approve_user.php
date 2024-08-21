<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}
include 'connectdb.php';

if (!isset($_GET['user_id'])) {
    header('Location: user_approvals.php');
    exit();
}

$user_id = $_GET['user_id'];

// Fetch user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $status = ($action == 'approve') ? 'approved' : 'rejected';

    $sql = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $user_id);

    if ($stmt->execute()) {
        // Redirect back to the user approvals page
        header('Location: user_approvals.php');
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
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

       


.card {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width: 100%;
    max-width: 800px;
    margin: 20px auto;
    display: grid;
    gap: 15px;
    grid-template-columns: 1fr 2fr;
}

h1 {
    grid-column: span 2;
    margin: 0;
    color: green;
    font-size: 24px;

    padding-bottom: 10px;
    margin-bottom: 20px;
}

.detail {
    display: contents;
}

.detail strong {
    font-weight: 600;
    color: green;
 
}


.detail p {
    margin: 0;
    color: green;
    
}

form {
    grid-column: span 2;
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

button {
    background-color: green;
    border: none;
    color: white;
    padding: 12px 20px;
    text-align: center;
    text-decoration: none;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    flex: 1;
    font-weight: bolder;
}

button:hover {
    background-color:yellowgreen;

}

button:active {
    transform: scale(1);
}

.back-link {
    grid-column: span 2;
    display: block;
    text-align: center;
    margin-top: 20px;
    text-decoration: none;
    color: green;
    font-size: 16px;
    font-weight: 500;
}

.back-link:hover {
    text-decoration: underline;
}
</style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="./drop.css">
    <title>GreenMart Dashboard</title>
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
            <li><a href="inventory_report.php"><i class="fas fa-warehouse"></i> Inventory Reports</a></li>
            <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
            <li><a href="logout.php?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

            </ul>
        </nav>
    </aside>
    <main>
    
<div class="card">
    <h1>User Details</h1>
    <div class="detail">
        <strong>First Name:</strong>
        <p><?php echo htmlspecialchars($user['firstname']); ?></p>
    </div>
    <div class="detail">
        <strong>Last Name:</strong>
        <p><?php echo htmlspecialchars($user['lastname']); ?></p>
    </div>
    <div class="detail">
        <strong>Username:</strong>
        <p><?php echo htmlspecialchars($user['username']); ?></p>
    </div>
    <div class="detail">
        <strong>Email:</strong>
        <p><?php echo htmlspecialchars($user['email']); ?></p>
    </div>
    <div class="detail">
        <strong>Mobile:</strong>
        <p><?php echo htmlspecialchars($user['mobile']); ?></p>
    </div>
    <div class="detail">
        <strong>Country:</strong>
        <p><?php echo htmlspecialchars($user['country']); ?></p>
    </div>
    <div class="detail">
        <strong>State:</strong>
        <p><?php echo htmlspecialchars($user['state']); ?></p>
    </div>
    <div class="detail">
        <strong>City:</strong>
        <p><?php echo htmlspecialchars($user['city']); ?></p>
    </div>
    <div class="detail">
        <strong>Address:</strong>
        <p><?php echo htmlspecialchars($user['address']); ?></p>
    </div>

    <form action="approve_user.php?user_id=<?php echo htmlspecialchars($user_id); ?>" method="POST">
        <button type="submit" name="action" value="approve">Approve</button>
        <button type="submit" name="action" value="reject">Reject</button>
    </form>

    <a href="user_approvals.php" class="back-link">Back to User Approvals</a>
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
