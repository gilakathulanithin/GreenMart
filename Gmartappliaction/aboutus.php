<?php
session_start();


include 'connectdb.php'; // Include database connection
$username=$_SESSION['username'];
// Fetch categories from the database
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
// Check if logout request is made
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    // Destroy the session and clear all session variables
    session_unset();
    session_destroy();

    // Redirect to the same page or another page
    header("Location: template.php"); // Adjust the redirection as needed
    exit();
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
    <main class="about-us">
    <!-- Hero Section -->
    <div class="hero">
        <h1>Welcome to GreenMart</h1>
        <p>Your one-stop destination for sustainable and organic products.</p>
    </div>

    <!-- Mission and Vision Section -->
    <section class="mission-vision">
        <div class="mission">
            <h2>Our Mission</h2>
            <img src="./assets//photos/mission.jpg" alt="Our Mission">
            <p>To provide the highest quality organic and sustainable products to our customers while promoting a healthier lifestyle and a greener planet.</p>
        </div>
        <div class="vision">
            <h2>Our Vision</h2>
            <img src="./assets//photos/vision.jpg" alt="Our Vision">
            <p>To be the leading supermarket that prioritizes sustainability, community, and the environment, ensuring a better future for generations to come.</p>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values">
        <h2>Our Values</h2>
        <ul>
            <li>
                <i class="fas fa-leaf"></i>
                <p>Eco-Friendly Products</p>
            </li>
            <li>
                <i class="fas fa-heart"></i>
                <p>Customer-Centric Service</p>
            </li>
            <li>
                <i class="fas fa-recycle"></i>
                <p>Commitment to Sustainability</p>
            </li>
        </ul>
    </section>

    <!-- History Section -->
    <section class="history">
    <div class="icon">
        <i class="fa fa-history" aria-hidden="true"></i>
    </div>
    <div>
        <h2>Our History</h2>
        <p>Founded in 2020, GreenMart has grown from a small neighborhood store to a leading supermarket known for its commitment to sustainability and customer satisfaction.</p>
    </div>
</section>

<!-- Community Involvement Section -->
<section class="community-involvement">
    <div class="icon">
        <i class="fa fa-hands-helping" aria-hidden="true"></i>
    </div>
    <div>
        <h2>Community Involvement</h2>
        <p>We believe in giving back to the community. GreenMart actively participates in local events and supports various environmental initiatives.</p>
    </div>
</section>

<!-- Contact Info Section -->
<section class="contact-info">
    <div class="icon">
        <i class="fa fa-phone" aria-hidden="true"></i>
    </div>
    <div>
        <h2>Contact Us</h2>
        <p>If you have any questions or feedback, feel free to reach out to us at <a href="mailto:info@greenmart.com">info@greenmart.com</a> or call us at +91 12345 67890.</p>
    </div>
</section>
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
       
        
function logout() {
    // Redirect to the logout script
    window.location.href = 'logoutcust.php';
}

    </script>
<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

h1, h2, h3 {
    font-weight: 700;
    margin-bottom: 15px;
    color: #2e7d32;
}

h1 {
    font-size: 2.5em;
}

h2 {
    font-size: 2em;
}

p, li {
    font-size: 1.1em;
}

/* Container for About Us Page */
.about-us {
    max-width: 1200px;
 margin: 10%;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

/* Hero Section */
.hero {
    background-color:black;
    color: #fff;
    padding: 50px 20px;
    text-align: center;
    border-radius: 10px 10px 0 0;
    background: black no-repeat center center/cover;
    box-shadow: inset 0 0 100px rgba(0, 0, 0, 0.2);
}

.hero h1 {
    margin: 0;
    font-size: 3em;
    letter-spacing: 2px;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
}

.hero p {
    font-size: 1.3em;
    margin-top: 10px;
}

/* Mission and Vision Section */
.mission-vision {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    padding: 40px 0;
}

.mission, .vision {
    flex: 1 1 48%;
    margin-bottom: 30px;
    text-align: center;
}

.mission h2, .vision h2 {
    border-bottom: 3px solid #4caf50;
    padding-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mission h2::before, .vision h2::before {
    content: "\f1ad"; /* Font Awesome icon (fa-bullseye) */
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    margin-right: 10px;
    color: #4caf50;
}

.vision h2::before {
    content: "\f201"; /* Font Awesome icon (fa-binoculars) */
}

.mission p, .vision p {
    padding: 10px;
    background-color: #e8f5e9;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.mission img, .vision img {
    max-width: 200px;
    margin: 20px auto;
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

/* Values Section */
.values {
    padding: 40px 20px;
    background-color: #f1f8e9;
    border-radius: 10px;
    margin: 30px 0;
    text-align: center;
    flex-direction: column;
}

.values h2 {
    margin-bottom: 30px;
    color: #2e7d32;
}

.values ul {
    list-style-type: none;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-around;
}

.values ul li {
    background: #fff;
    margin: 10px;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    flex: 1 1 30%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.values ul li:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.values ul li i {
    font-size: 2em;
    margin-bottom: 15px;
    color: #388e3c;
}

/* History, Community Involvement, and Contact Info Sections */
.history, .community-involvement, .contact-info {
    padding: 30px 20px;
    margin: 20px 0;
    background-color: #fafafa;
    border-radius: 10px;
    display: flex;
    align-items: center;
}

.history img, .community-involvement img, .contact-info img {
    max-width: 120px;
    margin-right: 20px;
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.contact-info p a {
    color: #388e3c;
    text-decoration: none;
    font-weight: bold;
    border-bottom: 2px solid transparent;
    transition: border-bottom 0.3s;
}

.contact-info p a:hover {
    border-bottom: 2px solid #388e3c;
}

/* Responsive Design */
@media (max-width: 768px) {
    .about-us {
    
 margin: 0%;
   
}
    .mission-vision {
        flex-direction: column;
    }

    .mission, .vision {
        width: 100%;
        margin-bottom: 20px;
    }

    .hero h1 {
        font-size: 2.5em;
    }

    .hero p {
        font-size: 1.2em;
    }

    .values ul li {
        flex: 1 1 48%;
    }

    .history, .community-involvement, .contact-info {
        flex-direction: column;
        text-align: center;
    }

    .history img, .community-involvement img, .contact-info img {
        margin-bottom: 20px;
        margin-right: 0;
    }
}

@media (max-width: 480px) {
    .hero h1 {
        font-size: 2em;
    }

    .hero p {
        font-size: 1em;
    }

    p, li {
        font-size: 1em;
    }

    .values ul li {
        flex: 1 1 100%;
    }
}
section {
    display: flex;
    align-items: center;
    padding: 20px;
    margin: 10px 0;
}

h2 {
    margin: 0;
    font-size: 1.5em;
}

/* Icon Styles */
.icon {
    font-size: 3em; /* Adjust the size as needed */
    color: #4CAF50; /* Adjust the color as needed */
    margin-right: 20px;
}

.icon i {
    display: block;
}

/* Section Styles */
.history, .community-involvement, .contact-info {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    background-color: #f9f9f9;
}

.history {
    background-color: #e8f5e9;
}

.community-involvement {
    background-color: #e3f2fd;
}

.contact-info {
    background-color: #f1f8e9;
}

/* Responsive Styles */
@media (max-width: 768px) {
    section {
        flex-direction: column;
        text-align: center;
    }

    .icon {
        margin: 0 0 10px 0;
    }
}
</style>