<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php'; // Include database connection

// Get product ID from the query string
$productId = intval($_GET['id']);

// Fetch product details from the database
$sqlProduct = "SELECT * FROM products WHERE id = ?";
$stmtProduct = $conn->prepare($sqlProduct);
$stmtProduct->bind_param("i", $productId);
$stmtProduct->execute();
$product = $stmtProduct->get_result()->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit();
}

// Function to fetch product images
function getProductImages($productId, $conn) {
    $query_images = "SELECT image_path FROM product_images WHERE product_id = ?";
    $stmt = $conn->prepare($query_images);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result_images = $stmt->get_result();
    $images = array();
    
    while ($image = $result_images->fetch_assoc()) {
        $images[] = 'uploads/' . basename($image['image_path']);
    }
    
    $stmt->close();
    return $images;
}

// Fetch product images
$images = getProductImages($productId, $conn);

// Handle Add to Cart
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $userId = $_SESSION['user_id']; // Ensure you have the user_id in session

    // Initialize the cart in the session if not already set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // Check if the product is already in the cart
    if (isset($_SESSION['cart'][$productId])) {
        // Increment quantity if product is already in the cart
        if ($_SESSION['cart'][$productId] < 6) {
            $_SESSION['cart'][$productId] += 1;
            $message = "Product quantity updated in cart!";
        } else {
            $message = "You can only add up to 6 units of this product.";
        }
    } else {
        // Add new product to cart
        $_SESSION['cart'][$productId] = 1;
        $message = "Product added to cart!";
    }
}

// Function to get the quantity of a specific product in the cart
function getCartQuantity($productId) {
    return isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0;
}

// Get cart quantity for this product
$cartQuantity = getCartQuantity($productId);

// Count total distinct products in cart
$totalCartItems = count($_SESSION['cart'] ?? []);

// Fetch rating counts
$sqlRatingCounts = "SELECT rating, COUNT(*) as count FROM product_rating WHERE productid = ? GROUP BY rating ORDER BY rating DESC";
$stmtRatingCounts = $conn->prepare($sqlRatingCounts);
$stmtRatingCounts->bind_param("i", $productId);
$stmtRatingCounts->execute();
$resultRatingCounts = $stmtRatingCounts->get_result();

// Initialize an array to hold the count for each rating (5 to 1)
$ratingCounts = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);

// Populate the array with the actual counts from the query
while ($row = $resultRatingCounts->fetch_assoc()) {
    $ratingCounts[$row['rating']] = $row['count'];
}

// Fetch comments
$sqlComments = "SELECT comment, rating FROM product_rating WHERE productid = ?";
$stmtComments = $conn->prepare($sqlComments);
$stmtComments->bind_param("i", $productId);
$stmtComments->execute();
$resultComments = $stmtComments->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" /> 
   
    <style>
main {
    margin-top: 10%;
    max-width: 800px;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    position: relative;
    left: 25%;
}

/* Cancel Icon */
.cancel-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 24px;
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.cancel-icon:hover {
    color: #e52e04;
}

.cancel-icon i {
    margin: 0;
}

/* Single Product Section */
.single-product {
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 500px; /* Fixed width */
    margin: 0 auto;
}

.product-content {
    width: 100%;
    text-align: center;
}

.single-product img {
    width: 100%;
    max-width: 300px;
    height: auto;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    margin-bottom: 20px;
}

.single-product img:hover {
    transform: scale(1.05);
}

.single-product h2 {
    font-size: 24px;
    color: black;
    margin: 0 0 15px;
}

.single-product p {
    margin: 10px 0;
    font-size: 16px;
    line-height: 1.6;
}

.product-price {
    font-size: 20px;
    color: #e52e04;
    margin: 15px 0;
}

/* Add to Cart Button */
.cart-button {
    display: inline-block;
    padding: 12px 20px;
    background-color: #fb641b;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
    text-transform: uppercase;
    margin-top: 10px;
}

.cart-button:hover {
    background-color: #d04b16;
    transform: scale(1.02);
}

/* Quantity Message */
.quantity {
    margin-top: 10px;
    color: #2874f0;
    font-weight: bold;
}

/* Error or Success Message */
.message {
    margin-top: 10px;
    color: #e52e04;
}

/* Reviews Section */

/* Responsive Design */
@media (max-width: 768px) {
    .single-product img {
        max-width: 100%;
    }

    .single-product {
        padding: 15px;
    }

    
}

/* Reviews Section */
.reviews {
    margin: 30px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    max-width: 900px;
}

.reviews h3 {
    font-size: 26px;
    color: green;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 600;
}

.reviews h4 {
    font-size: 20px;
    color: green;
    margin: 15px 0;
    text-align: center;
    font-weight: 500;
}

.rating-distribution {
    margin-bottom: 30px;
   
}

.rating-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
}

.rating-icon {
    color: #f5c518;
    font-size: 20px;
    margin-right: 12px;
    padding-left: 35%;
}

.rating-item p {
    margin: 0;
    font-size: 18px;
    color: #333;
    font-weight: 400;
  
}

.customer-reviews {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 10px;
}

.review {
    padding: 15px;
    border-bottom: 1px solid #ddd;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin-bottom: 15px;
}

.review:last-child {
    border-bottom: none;
}

.review strong {
    color: #2874f0;
    font-size: 18px;
    display: block;
    margin-bottom: 8px;
}

.review p {
    margin: 5px 0;
    font-size: 16px;
    line-height: 1.6;
    color: #666;
}

.customer-reviews p {
    text-align: center;
    color: #888;
    font-size: 18px;
    font-style: italic;
}

/* Responsive Design */
@media (max-width: 768px) {
    .reviews {
        padding: 15px;
    }

    .rating-item {
        flex-direction: column;
        align-items: flex-start;
        padding: 12px;
    }

    .rating-icon {
        margin-bottom: 8px;
    }

    .review {
        padding: 12px;
        margin-bottom: 12px;
    }
}

    </style>
</head>
<body>
    <!-- Header -->
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
                    <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0) : ?>
                        <span class="cart-count"><?php echo $_SESSION['cart_count']; ?></span>
                    <?php endif; ?>
                </a>
                  <div class="profile">
                         <?php if (isset($_SESSION['username'])) : ?>
                             <!-- If user is logged in, show profile button and dropdown -->
                             <button class="profile-btn" aria-haspopup="true" aria-controls="profile-menu" onclick="toggleProfileMenu()">
                                 <i class="fas fa-user"></i>
                             </button>
                             <div class="profile-menu" id="profile-menu">
                                 <a href="#"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                                 <a href="logoutcust.php" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
    <section class="single-product">
        <a href="window.history.back();" class="cancel-icon">
            <i class="fa fa-times" aria-hidden="true"></i>
        </a>
        <div class="product-content">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <img src="<?php echo !empty($images) ? htmlspecialchars($images[0]) : 'https://via.placeholder.com/300'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <p><strong>Price:</strong> ₹<?php echo htmlspecialchars($product['price']); ?></p>
            <!-- Add to Cart Form -->
            <form action="" method="post">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productId); ?>">
                <button type="submit" name="add_to_cart" class="cart-button">Add to Cart</button>
                <?php if ($cartQuantity > 0): ?>
                    <p class="quantity">In Cart: <?php echo $cartQuantity; ?></p>
                <?php endif; ?>
                <?php if (!empty($message)): ?>
                    <p class="message"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>
            </form>
        </div>
    </section>

   <!-- Reviews Section -->
<section class="reviews">
    <h3>Product Reviews</h3>
    
    <div class="rating-distribution">
        <h4>Rating Distribution:</h4>
       
       <?php
        // Display rating counts from 5 to 1 with icons
        for ($i = 5; $i >= 1; $i--) {
            echo "<div class='rating-item'>";
            echo "<i class='fa fa-star rating-icon'></i>";
            echo "<p>Rating: $i - Count: " . $ratingCounts[$i] . "</p>";
            echo "</div>";
        }
        ?>
      
    </div>

    <div class="customer-reviews">
        <h4>Customer Reviews:</h4>
        <?php
        // Display comments
        if ($resultComments->num_rows > 0) {
            while ($row = $resultComments->fetch_assoc()) {
                echo "<div class='review'>";
                echo "<p><strong>Rating: " . htmlspecialchars($row['rating']) . "</strong></p>";
                echo "<p>" . htmlspecialchars($row['comment']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No reviews yet.</p>";
        }
        ?>
    </div>
</section>
</main>



    <!-- Footer -->
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
    document.addEventListener("DOMContentLoaded", function() {
        const messageElement = document.querySelector('.message');
        if (messageElement) {
            setTimeout(() => {
                messageElement.style.display = 'none';
            }, 3000); // 3 seconds
        }
    });
</script>
