<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

include 'connectdb.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $user_id = $_SESSION['user_id'];

    // Validate the input
    if (strlen($current_password) < 6 || strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        // Fetch the current password from the database
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $error = "User not found.";
        } else {
            $row = $result->fetch_assoc();
            $stored_password = $row['password'];

            if ($current_password !== $stored_password) {
                $error = "Current password is incorrect.";
            } else {
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die('Prepare failed: ' . $conn->error);
                }
                $stmt->bind_param('si', $new_password, $user_id);
                if ($stmt->execute()) {
                    $success = "Password updated successfully.";
                } else {
                    $error = "Failed to update password.";
                }
            }
        }
    }
}

// Fetch the count of pending approvals
$sql = "SELECT COUNT(*) AS pending_count FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pending_count = $row['pending_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="drop.css">
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .change-password-form {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .change-password-form h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .change-password-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .change-password-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .change-password-form button {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .change-password-form button:hover {
            background-color: #218838;
        }

        .message {
            margin: 20px 0;
            padding: 10px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<header>
    <div class="header-content">
        <label for="menu-toggle" id="menu-toggle-label"><i class="fas fa-bars"></i></label>
        <input type="checkbox" id="menu-toggle" hidden>
        <h1><span>GreenMart</span></h1>
    </div>
</header>

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
        <div class="change-password-form">
            <h2>Change Password</h2>
            <?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (isset($success)): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

            <form method="POST" action="change_password.php" onsubmit="return validateForm()">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" >

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" >

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" >

                <button type="submit">Change Password</button>
            </form>
        </div>
    </main>
</div>

<footer class="footer">
    <p>&copy; 2024 GreenMart. All Rights Reserved.</p>
</footer>

<script>
    function validateForm() {
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            alert('New passwords do not match.');
            return false;
        }
        if (currentPassword.length < 6 || newPassword.length < 6) {
            alert('Password must be at least 6 characters long.');
            return false;
        }
        return true;
    }

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
        const sidebar = document.querySelector('#sidebar');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    });
</script>
</body>
</html>
