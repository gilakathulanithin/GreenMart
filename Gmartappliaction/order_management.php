<?php
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

include 'connectdb.php';

// Fetch the counts for orders
$sql_all_orders = "SELECT COUNT(*) AS all_orders_count FROM orders";
$sql_pending_orders = "SELECT COUNT(*) AS pending_orders_count FROM orders WHERE status = 'pending'";
$sql_delivered_orders = "SELECT COUNT(*) AS delivered_orders_count FROM orders WHERE status = 'delivered'";

$result_all_orders = $conn->query($sql_all_orders);
$result_pending_orders = $conn->query($sql_pending_orders);
$result_delivered_orders = $conn->query($sql_delivered_orders);

$all_orders_count = $result_all_orders->fetch_assoc()['all_orders_count'];
$pending_orders_count = $result_pending_orders->fetch_assoc()['pending_orders_count'];
$delivered_orders_count = $result_delivered_orders->fetch_assoc()['delivered_orders_count'];
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="./drop.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content {
            flex: 1;
            padding: 20px;
            margin-left: 250px; /* Adjusts content to accommodate the sidebar */
            background: #ecf0f1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    
        .main-content {
            margin-left: 10%;
            margin-right: 10%;
        margin-top: 5%;
            width: 80%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap:2%;
        }
    
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: box-shadow 0.3s;
        }
    
        .card img {
            width: 100%;
            height: 80px; /* Fixed height for images */
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
    
        .card h3 {
            margin: 10px 0;
            font-size: 20px;
            color: #333;
        }
    
        .card p {
            font-size: 18px;
            color: #777;
        }
    
        .card:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        @media (max-width: 768px) {
              #menu-toggle-label {
        display: block; /* Show the toggle button */
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        transform: translateX(-100%); /* Hide sidebar by default */
    }

    .sidebar.active {
        transform: translateX(0); /* Show sidebar when active */
    }

       
            .main-content {
            width: 100%;
            max-width: 400px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .content {
            margin-left: 0; /* Adjust content margin for small screens */
        }
    }
    @media (max-width: 576px) {
        #menu-toggle-label {
        display: block; /* Show the toggle button */
    }

    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        transform: translateX(-100%); /* Hide sidebar by default */
    }

    .sidebar.active {
        transform: translateX(0); /* Show sidebar when active */
    }

    .content {
        margin-left: 0; /* Ensure content takes full width */
    }
        .card {
            padding: 15px;
        }

        .card img {
            height: 60px; /* Adjust image size for smaller screens */
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
            <h1>ORDER MANAGEMENT</h1>
            <section class="main-content">
                <div class="card">
                    <h3>All Orders</h3>
                    <p><?php echo $all_orders_count; ?></p>
                </div>
                <div class="card">
                    <h3>Pending Orders</h3>
                    <p><?php echo $pending_orders_count; ?></p>
                </div>
                <div class="card">
                    <h3>Delivered Orders</h3>
                    <p><?php echo $delivered_orders_count; ?></p>
                </div>
            </section>
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
    const label = document.getElementById('menu-toggle-label');

    menuToggle.addEventListener('change', function() {
        if (this.checked) {
            sidebar.classList.add('active');
        } else {
            sidebar.classList.remove('active');
        }
    });

    label.addEventListener('click', function() {
        menuToggle.checked = !menuToggle.checked;
        if (menuToggle.checked) {
            sidebar.classList.add('active');
        } else {
            sidebar.classList.remove('active');
        }
    });
});

    </script>
</body>
</html>
