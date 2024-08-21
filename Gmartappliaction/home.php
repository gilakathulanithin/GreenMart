<?php
session_start();
// if (!isset($_SESSION['username'])) {
//     header("Location: home.php");
//     exit();
// }

include 'connectdb.php'; // Include database connection
$username=$_SESSION['username'];
// Fetch categories from the database
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);

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

   
       <!-- Hero Section -->
       <section class="hero">
        <h1>Welcome to GreenMart</h1>
      <br><br>
        <a href="product.php">Shop Now</a>
    </section>

  <center><h1 style="font-weight:bolder;letter-spacing:0.3rem;" class="catheading">products by catogery</h1></center>
    <!-- Categories Section -->
  <section class="categories">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div>
                    <a href="katogery.php?id=<?php echo urlencode($row['id']); ?>">
                    <img src="images/categories/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p>Explore our range of <?php echo htmlspecialchars($row['name']); ?>.</p>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No categories available.</p>
        <?php endif; ?>
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
       
        


    </script>
<style>
    main {
    padding: 60px 20px;
  
}
.catheading{
    /* background-color: red; */
    letter-spacing: 0.2rem;
    word-spacing: 0.5rem;
    text-shadow: 2px 2px 2px green;
    font-family: 'Times New Roman', Times, serif;
    background-color: whitesmoke;
}
/* Hero Section Styling */
.hero {
    background: url('./assets//photos/picture.jpg');
    background-size: cover;
    backdrop-filter: 50%;
    color: white;
    text-align: center;
    padding: 80px 20px;
    border-radius: 10px;
    margin-bottom: 40px;
}

.hero h1 {
    font-size: 2.5em;
    margin-bottom: 20px;
    background-color: rgba(255, 255, 255, 0.511);
    color: black;
    text-shadow: 3px 3px 3px green;
   
}

.hero p {
    font-size: 1.2em;
    margin-bottom: 30px;
    font-weight: bolder;
    background-color:#00510452;
}

.hero a {
    background-color: #2e7d32;
    text-decoration: none;
    font-weight: bolder;
    color: white;
    padding: 15px 30px;
    font-size: 1em;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.3s;
}

.hero a:hover {
    background-color: #1b5e20;
    transform: scale(1.05);
}

/* Categories Section Styling */
.categories {
    margin: 5%;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 40px;

}

.categories div {
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s, box-shadow 0.3s;
}


.categories img {
    width: 100%;
    height: auto;
    border-radius: 50px;
    margin-bottom: 15px;
}

.categories h3 {
    font-size: 1.2em;
    margin-bottom: 10px;
    color: #2e7d32;
}

.categories p {
    font-size: 1em;
    color: #666;
}

.categories a {
    text-decoration: none;
    color: inherit;
    display: block;
}

/* Media Queries for Responsiveness */
@media (max-width: 1200px) {
    main{
        padding: auto;
        margin-top: 20%;
    }
    .hero h1 {
        font-size: 2.2em;
    }

    .hero p {
        font-size: 1.1em;
    }
}

@media (max-width: 992px) {
    main{
        padding: auto;
        margin-top: 20%;
    }
    .hero h1 {
        font-size: 2em;
    }

    .hero p {
        font-size: 1em;
    }

    .hero button {
        padding: 12px 25px;
        font-size: 0.9em;
    }
}

@media (max-width: 768px) {
    main{
        padding: auto;
        margin-top: 20%;
    }
    .hero h1 {
        font-size: 1.8em;
    }

    .hero p {
        font-size: 0.9em;
    }

    .hero button {
        padding: 10px 20px;
        font-size: 0.85em;
    }
}

@media (max-width: 576px) {
    main{
        padding: auto;
        margin-top: 20%;
    }
    .hero h1 {
        font-size: 1.5em;
    }

    .hero p {
        font-size: 0.85em;
    }

    .hero button {
        padding: 8px 15px;
        font-size: 0.8em;
    }

    .categories {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .categories h3 {
        font-size: 1em;
    }

    .categories p {
        font-size: 0.9em;
    }
}
</style>