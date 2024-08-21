<?php
session_start();
include 'connectdb.php';  // Include the database connection script

// Check if the user is logging out
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: home.php");
    exit();
}

// Count total distinct products in cart
$totalCartItems = count($_SESSION['cart'] ?? []);




?>
<style>
/* Main Container */
main {
    padding: 20px;
    text-align: center;
}

/* Profile Heading */
main h1 {
    font-size: 2.5em;
    color: #2e7d32; /* Green color for consistency */
    margin-bottom: 40px; /* Space below the heading */
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px; /* Added letter spacing for better readability */
}

/* Card Container */
.card {
    display: inline-block;
    background-color: #ffffff; /* White background for each card */
    border: 1px solid #ddd; /* Light border for each card */
    border-radius: 12px; /* Slightly rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    width: 220px; /* Fixed width for each card */
    margin: 20px;
    padding: 20px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card i {
    font-size: 2.5em; /* Adjusted icon size for better fit */
    color: #4CAF50; /* Green color for icons */
    margin-bottom: 15px;
}

.card h3 {
    margin: 10px 0;
    font-size: 1.2em;
    color: #333; /* Darker text color for better readability */
}

.card a {
    text-decoration: none;
    color: #333;
    font-weight: bold;
    transition: color 0.3s ease, text-decoration 0.3s ease;
}

.card a:hover {
    color: #2e7d32; /* Match the heading color on hover */
    text-decoration: underline;
}

.card:hover {
    transform: translateY(-5px); /* Slightly raise the card on hover */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Deeper shadow on hover */
}

/* Responsive Design */
@media (max-width: 768px) {
    .card {
        width: 90%; /* Adjust width for vertical layout */
    }
}

@media (max-width: 480px) {
    .card i {
        font-size: 2em; /* Smaller icon size for very small screens */
    }

    .card h3 {
        font-size: 1em; /* Smaller heading size for very small screens */
    }
}
</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenMart - Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Include external CSS file -->
    <script src="scripts.js" defer></script> <!-- Include external JS file -->
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
                <a href="contact.php"><i class="fas fa-envelope"></i> <pre>Contact Us</pre></a>
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
<h1>MY profile </h1>
<div class="card">
       
      
       <i class="fas fa-home"></i>
       <h3>  <a href="home.php"> Back to Home</a></h3>

   </div>
        <div class="card">
            <i class="fa fa-key"></i>
            <h3><a href="cust_changepassword.php">changepassword</a></h3>
        </div>
        <div class="card">
            <i class="fa fa-search"></i>
            <h3><a href="track_orders.php">Track Orders</a></h3>
        </div>
        <div class="card">
        <i class="fa fa-exclamation-circle"></i>
        <h3><a href="submitcomplaint.php"> Report a Complaint</a></h3>
            
        </div>
        <div class="card">
             <i class="fa fa-file-alt"></i>
             <h3><a href="complaintstatus.php"> Complaint Status</a></h3>
            </div>
        <div class="card">
            <i class="fa fa-shopping-cart"></i>
            <h3><a href="myorders.php">My Orders</a></h3>
        </div>
        <div class="card">
            <i class="fa fa-sign-out-alt"></i>
            <h3> <a href="logout.php?logout=true"> Logout</a></h3>
        </div>
       
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
    <!-- Hidden div for error messages -->
    <div id="error-message" style="display: none;"></div>
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



