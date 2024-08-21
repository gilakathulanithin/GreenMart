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
// Fetch complaints from the database
$complaints_sql = "SELECT c.id, c.customer_id, CONCAT(u.firstname, ' ', u.lastname) as user_name, c.complaint_text, c.reply_text, c.complaint_date, c.reply_date, c.status
                   FROM complaints c
                   JOIN users u ON c.customer_id = u.id
                   ORDER BY c.complaint_date DESC";
$complaints_result = $conn->query($complaints_sql);

if (!$complaints_result) {
    die("Error executing complaints query: " . $conn->error);
}

$complaints = array();
if ($complaints_result->num_rows > 0) {
    $complaints = $complaints_result->fetch_all(MYSQLI_ASSOC);
}

// Handle admin reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $reply_text = $conn->real_escape_string($_POST['reply_text']);

    // Update the complaint with the admin's reply
    $update_sql = "UPDATE complaints SET reply_text = '$reply_text', reply_date = NOW(), status = 'resolved' WHERE id = $complaint_id";
    
    if ($conn->query($update_sql) === TRUE) {
        echo "Complaint resolved successfully!";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

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
            height: 80vh;
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
/* General Styles for the Content */
.content {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow-x: auto; /* Allows horizontal scrolling on smaller screens */
}

/* Heading Style */
.content h1 {
    font-size: 2em;
    margin-bottom: 20px;
    color: #333;
    text-align: center;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 auto;
    background-color: green;
    border-radius: 8px;
    overflow: hidden; /* Ensures rounded corners work */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

thead {
    background-color: green; /* Blue background for the header */
    color: #fff;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    font-weight: bold;
}

tbody tr:nth-child(even) {
    background-color: #f2f2f2; /* Zebra striping for rows */
}

tbody tr.pending {
    background-color: #fff3cd; /* Light yellow for pending status */
}

tbody tr.resolved {
    background-color: #d4edda; /* Light green for resolved status */
}

tbody tr td:last-child {
    text-align: center;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

textarea {
    width: 100%;
    padding: 9px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical; /* Allows vertical resizing only */
    min-height: 60px;
}

button {
    background-color: green; /* Blue background for buttons */
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.3s;
}



/* Message for No Complaints */
p {
    text-align: center;
    color: #666;
    font-size: 1.2em;
}

/* Responsive Design */
@media (max-width: 768px) {
    .content {
        padding: 15px;
    }

    table {
        font-size: 14px; /* Smaller font size for smaller screens */
    }

    th, td {
        padding: 8px;
    }

    textarea {
        min-height: 40px; /* Adjust min-height for smaller screens */
    }

    button {
        padding: 8px 15px;
        font-size: 0.9em;
    }
}

@media (max-width: 480px) {
    .content {
        padding: 10px;
    }

    table {
        font-size: 12px; /* Further reduce font size */
    }

    th, td {
        padding: 6px;
    }

    textarea {
        min-height: 30px; /* Adjust min-height further */
    }

    button {
        padding: 6px 12px;
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
        <h1>Customer Complaints</h1>

<?php if (count($complaints) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Complaint</th>
                <th>Reply</th>
                <th>Complaint Date</th>
                <th>Reply Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($complaints as $complaint): ?>
                <tr class="<?= $complaint['status'] ?>">
                    <td><?= $complaint['id'] ?></td>
                    <td><?= $complaint['user_name'] ?></td>
                    <td><?= $complaint['complaint_text'] ?></td>
                    <td><?= $complaint['reply_text'] ?: 'No reply yet' ?></td>
                    <td><?= $complaint['complaint_date'] ?></td>
                    <td><?= $complaint['reply_date'] ?: 'N/A' ?></td>
                    <td><?= ucfirst($complaint['status']) ?></td>
                    <td>
                        <?php if ($complaint['status'] === 'pending'): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="complaint_id" value="<?= $complaint['id'] ?>">
                                <textarea name="reply_text" placeholder="Type your reply here..." required></textarea>
                                <button type="submit" name="reply">Reply</button>
                            </form>
                        <?php else: ?>
                            Resolved
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No complaints to show.</p>
<?php endif; ?>
               
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
