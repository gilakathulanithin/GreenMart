<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

include 'connectdb.php';

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the count of pending approvals
$sql_pending = "SELECT COUNT(*) AS pending_count FROM users WHERE status = 'pending'";
$result_pending = $conn->query($sql_pending);

if ($result_pending) {
    $row_pending = $result_pending->fetch_assoc();
    $pending_count = $row_pending['pending_count'];
} else {
    echo "Error: " . $conn->error;
}

// Fetch the total number of users
$sql_total = "SELECT COUNT(*) AS total_users FROM users";
$result_total = $conn->query($sql_total);

if ($result_total) {
    $row_total = $result_total->fetch_assoc();
    $total_users = $row_total['total_users'];
} else {
    echo "Error: " . $conn->error;
}

// Fetch the count of users with role 'users'
$sql_role_users = "SELECT COUNT(*) AS role_users_count FROM users WHERE role = 'user'";
$result_role_users = $conn->query($sql_role_users);

if ($result_role_users) {
    $row_role_users = $result_role_users->fetch_assoc();
    $role_users_count = $row_role_users['role_users_count'];
} else {
    echo "Error: " . $conn->error;
}
// SQL query to fetch the count of pending complaints
$query_pending_complaints = "SELECT COUNT(*) AS total_pending_complaints FROM complaints WHERE status = 'pending'";
$result_pending_complaints = $conn->query($query_pending_complaints);

if ($result_pending_complaints) {
    $data_pending_complaints = $result_pending_complaints->fetch_assoc();
    $pending_complaints_count = $data_pending_complaints['total_pending_complaints'];
} else {
    echo "Error: " . $conn->error;
}

// Close the database connection
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./drop.css">
    <link rel="stylesheet" href="./style.css">
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
            height: 90vh;
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
    

        @media (max-width: 768px) {
       
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
        
    }
    /* Card Container */
.card {
    background-color: gree; /* White background for the card */
    border: 1px solid #e0e0e0; /* Slightly darker gray border */
    border-radius: 12px; /* More rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Slightly stronger shadow for depth */
    padding: 20px; /* Padding inside the card */
    margin: 20px; /* Margin outside the card */
    max-width: 350px; /* Increased maximum width */
    text-align: center; /* Center-align text */
    transition: transform 0.3s, box-shadow 0.3s; /* Smooth transition for hover effects */
    overflow: hidden; /* Ensure content does not overflow */
}

/* Card Title */
.card h3 {

    font-size: 1.6em; /* Slightly larger font size for the title */
    color: #333; /* Dark text color */
    margin-bottom: 15px; /* More space below the title */
    font-weight: 600; /* Slightly bolder font */
    letter-spacing: 0.5px; /* Add a bit of letter spacing */
    font-weight: bolder;
}

/* Card Content */
.card p {
    font-size: 2.2em; /* Larger font size for the number */
    color: #007bff; /* Blue color for the number */
    margin: 0; /* Remove default margin */
    font-weight: 700; /* Bold font for emphasis */
}

/* Hover Effects */
/* .card:hover {
    transform: translateY(-8px); /* Slightly more pronounced lift effect on hover */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Stronger shadow on hover */
    background-color: #f9f9f9; /* Light background change on hover */
} */

/* Responsive Design */
@media (max-width: 600px) {
    .card {
        max-width: 100%; /* Full width on small screens */
        margin: 10px; /* Reduce margin on small screens */
    }

    .card h3 {
        font-size: 1.4em; /* Adjust font size for smaller screens */
    }

    .card p {
        font-size: 1.8em; /* Adjust font size for smaller screens */
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
            <h1>Admindashboard</h1>
            <!-- <center><h1><?php echo htmlspecialchars($_SESSION['username']); ?></h1></center> -->
            <section class="main-content">
               

                <!-- Data and Statistics -->
                 <div class="card">
                    <h3>Total Number of customers</h3>
                    <p><?php echo  $role_users_count; ?></p>
                   
                </div>
                <div class="card">
                  
                    <h3>Total Number of  status pending customers.</h3>
                    <p><?php echo $pending_count; ?></p>
                </div>
                <div class="card">
                  
                    <h3>customer complaints</h3>
                    <p><?php echo  $pending_complaints_count;?></p>
                <button style="width:30%;background-color:green; ">    <a href="view_complaint.php" style="text-decoration:none;color:white;font-weight:bolder;">view</a></button>
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
        
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
