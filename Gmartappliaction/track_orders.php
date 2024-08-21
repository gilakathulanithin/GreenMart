<?php 
session_start(); 
include 'connectdb.php'; 
 // Include the database connection script

// Check if the user is logging out
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: home.php");
    exit();
}
$search = isset($_GET['search']) ? $_GET['search'] : '';
// 
// Handle order tracking form submission
$orderStatus = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['order_id']);
    if ($orderId > 0) {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($order) {
            $orderStatus = $order['status'];
        } else {
            $orderStatus = 'Order not found.';
        }
    }
}
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
    <title>Track Order</title>
   
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

   .track-order {

       
            padding: 40px 20px;
            max-width: 600px;
        
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
          margin: 10%;
          position: relative;
          left: 20%;
      pos
        }

        .track-order h1 {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .back{
            margin-top: 10%;
        }       
.back a{
    color: green;

    padding: 10px;

    color: white;
    font-weight: bolder;
}
        .btn {
            padding: 10px 20px;
            background-color: green;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: bolder;
        }

        .btn:hover {
            background-color: #27ae60;
        }

        .order-status {
            margin-top: 20px;
        }

        .order-status h2 {
            margin-bottom: 10px;
        }

        .order-status p {
            font-size: 1.1em;
            color: #2ecc71;
        }
        .footer{
            width: 100%;
            position: absolute;
            bottom: 0%;
            background-color: green;
        }

      
    </style>
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
    <main class="track-order">
        <h1>Track Your Order</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="order_id">Enter Your Order Number:</label>
                <input type="text" id="order_id" name="order_id" >
            </div>
            <button type="submit" class="btn ">Track Order</button>
        </form>
        <?php if ($orderStatus): ?>
            <div class="order-status">
                <h2>Order Status:</h2>
                <p><?php echo htmlspecialchars($orderStatus); ?></p>
            </div>
        <?php endif; ?>
     <center> <a href="cust_dashboard.php" style="color:green;">back to profile</a></center>
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