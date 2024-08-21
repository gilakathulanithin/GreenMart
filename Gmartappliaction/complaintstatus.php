<?php
session_start();
// if (!isset($_SESSION['username'])) {
//     header("Location: home.php");
//     exit();
// }

include 'connectdb.php'; // Include database connection
$username=$_SESSION['username'];

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 1;

// Query to fetch complaints with replies for the given customer ID
$sql = "SELECT id, customer_id, complaint_text, reply_text, complaint_date, reply_date, status
        FROM complaints
        WHERE customer_id = ? AND reply_text IS NOT NULL AND reply_text != ''";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();


// Count total distinct products in cart
$totalCartItems = count($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Document</title>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
               <img src="./assets//logo/logo-no-background.png" alt="" width="180px" height="100px" style="color: rgb(9, 9, 9);">
            </div>
           &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
            <nav class="nav">
                
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search...">
                    <button class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <a href="home.php"><i class="fas fa-home"></i> <pre>Home</pre></a>
                <a href="myorders.php"><i class="fas fa-receipt"></i> <pre> my orders</pre></a>
                <a href="contactus.php"><i class="fas fa-envelope"></i> <pre>Contact Us</pre></a>
                <a href="aboutus.php"><i class="fas fa-info-circle"></i> <pre>AboutUs</pre></a>
                <a href="view_cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo $totalCartItems; ?></span>
                </a>
                  <div class="profile">
                         <?php if (isset($_SESSION['username'])) : ?>
                             <!-- If user is logged in, show profile button and dropdown -->
                             <button class="profile-btn" aria-haspopup="true" aria-controls="profile-menu" onclick="toggleProfileMenu()">
                                 <i class="fas fa-user"></i>
                             </button>
                             <div class="profile-menu" id="profile-menu">
                                 <a href="cust_dashboard.php"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                                 <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                             </div>
                         <?php else : ?>
                             <!-- If user is not logged in, show login button -->
                             <a class="login.php" href="login.php" style="text-decoration:none; color:green; font-weight:bolder;">
                                 <i class="fas fa-sign-in-alt"></i> Login
                             </a>
                         <?php endif; ?>
                </div>
            </nav>
          
            <div class="menu-toggle" aria-label="Toggle Menu" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>
<main>

<h1>View complaints status</h1>
    
    <table>
        <thead>
            <tr>
                <!-- <th>ID</th>
                <th>Customer ID</th> -->
                <th>Complaint </th>
                <th>Reply </th>
                <th>Complaint Date</th>
                <th>Reply Date</th>
                <!-- <th>Status</th> -->
                 <!-- <th>action</th> -->
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status_class = ($row['status'] === 'resolved') ? 'status-resolved' : 'status-pending';
                    echo "<tr>";
                    // echo "<td>{$row['id']}</td>";
                    // echo "<td>{$row['customer_id']}</td>";
                    echo "<td>" . htmlspecialchars($row['complaint_text']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['reply_text']) . "</td>";
                    echo "<td>{$row['complaint_date']}</td>";
                    echo "<td>{$row['reply_date']}</td>";
                    // echo "<td class='$status_class'>" . htmlspecialchars($row['status']) . "</td>";
            
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No complaints with replies found for this customer.</td></tr>";
            }
            ?>
        </tbody>
    </table>

<?php
// Close the connection
$stmt->close();
$conn->close();
?>
<center><a href="cust_dashboard.php" style=" color:green;">back to profile</a></center>

</main>

<footer>
    <div class="footerContainer">
        <div class="socialIcons">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <div class="footerNav">
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Contact us</a></li>
                <li><a href="#">Help</a></li>
            </ul>
        </div>
        <div class="footerBottom">
            <p>&copy; 2024 GreenMart. All rights reserved.</p>
           
        </div>
    </div>
</footer>
</body>
</html>

    <script>
         function toggleProfileMenu() {
        var menu = document.getElementById('profile-menu');
        menu.classList.toggle('show');
    }
        
        function toggleMenu() {
            const nav = document.querySelector('.nav');
            nav.classList.toggle('active');
        }
       
        


    </script>
<style>

main{
   height: 60vh;
   overflow: auto;
}

h1 {
    text-align: center;
    margin: 20px 0;
    color: #333;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px auto;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

thead {
    background-color: green;
    color: white;
}

th, td {
    padding: 12px 15px;
    text-align: left;
}

th {
    text-align: center;
    font-weight: bolder;
}

tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

tbody tr:hover {
    background-color: #f1f1f1;
}

/* Status Indicator Styles */
.status-resolved {
    color: #28a745;
}

.status-pending {
    color: #dc3545;
}

/* Responsive Styles */
@media (max-width: 768px) {
    main{
        margin-top: 35%;
    }
    table {
        font-size: 14px;
    }
    th, td {
        padding: 10px 8px;
    }
    h1 {
        font-size: 1.5em;
    }
}

@media (max-width: 480px) {
    table {
        font-size: 12px;
    }
    th, td {
        padding: 8px 6px;
    }
    h1 {
        font-size: 1.2em;
    }
}

</style>