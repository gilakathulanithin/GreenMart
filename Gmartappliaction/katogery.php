<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'connectdb.php'; // Include database connection

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

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $productId = intval($_POST['product_id']);
    $message = '';

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    if (isset($_SESSION['cart'][$productId])) {
        if ($_SESSION['cart'][$productId] < 6) {
            $_SESSION['cart'][$productId] += 1;
            $message = "Product quantity updated in cart!";
        } else {
            $message = "You can only add up to 6 units of this product.";
        }
    } else {
        $_SESSION['cart'][$productId] = 1;
        $message = "Product added to cart!";
    }

    // Calculate the quantity of the specific product in the cart
    $productQuantity = $_SESSION['cart'][$productId];

    // Calculate total distinct products in the cart
    $distinctProductCount = count($_SESSION['cart']);

    $response = array(
        'success' => true,
        'message' => $message,
        'quantity' => $productQuantity,
        'distinctProductCount' => $distinctProductCount
    );

    echo json_encode($response);
    exit();
}

// Function to get the quantity of a specific product in the cart
function getCartQuantity($productId) {
    return isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0;
}

// Fetch category details from the database
$categoryId = intval($_GET['id']);
$sqlCategory = "SELECT * FROM categories WHERE id = ?";
$stmtCategory = $conn->prepare($sqlCategory);
$stmtCategory->bind_param("i", $categoryId);
$stmtCategory->execute();
$category = $stmtCategory->get_result()->fetch_assoc();

if (!$category) {
    echo "Category not found.";
    exit();
}

// Fetch products related to the category
$sqlProducts = "SELECT * FROM products WHERE category_id = ?";
$stmtProducts = $conn->prepare($sqlProducts);
$stmtProducts->bind_param("i", $categoryId);
$stmtProducts->execute();
$products = $stmtProducts->get_result();

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
                                 <a href="#"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a>
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
    <!-- Category Products Section -->
    <main>
        <section class="category-products">
            <h2>Products in <?php echo htmlspecialchars($category['name']); ?></h2>
            <?php if ($products->num_rows > 0): ?>
                <div class="products-grid">
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <?php 
                        $images = getProductImages($product['id'], $conn); 
                        $cartQuantity = getCartQuantity($product['id']);
                        ?>
                        <div class="product-card">
                            <a href="thatpage.php?id=<?php echo $product['id']; ?>" class="product-image-link">
                                <img src="<?php echo !empty($images) ? htmlspecialchars($images[0]) : 'https://via.placeholder.com/220'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <div class="product-info">
                                <h3><a href="thatpage.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                <!-- <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p> -->
                                <p class="product-price">₹<?php echo htmlspecialchars($product['price']); ?></p>
                                <form id="add-to-cart-form-<?php echo $product['id']; ?>" onsubmit="addToCart(event, <?php echo $product['id']; ?>)">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                    <button class="cart-button" type="submit">Add to Cart</button>
                                    <?php if ($cartQuantity > 0): ?>
                                        <p class="quantity" id="quantity-<?php echo $product['id']; ?>">In Cart: <?php echo $cartQuantity; ?></p>
                                    <?php endif; ?>
                                    <p class="message" id="message-<?php echo $product['id']; ?>"></p>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No products available in this category.</p>
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

    <script>
 function toggleProfileMenu() {
        var menu = document.getElementById('profile-menu');
        menu.classList.toggle('show');
    }
        
        function toggleMenu() {
            const nav = document.querySelector('.nav');
            nav.classList.toggle('active');
        }
       
    

        function addToCart(event, productId) {
    event.preventDefault();
    
    const form = document.getElementById('add-to-cart-form-' + productId);
    const formData = new FormData(form);
    formData.append('action', 'add_to_cart');

    fetch('', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display the success message
            const messageElement = document.getElementById('message-' + productId);
            messageElement.innerText = data.message;

            // Check if the quantity element exists, and update it or create it
            let quantityElement = document.getElementById('quantity-' + productId);
            if (!quantityElement) {
                quantityElement = document.createElement('p');
                quantityElement.id = 'quantity-' + productId;
                quantityElement.classList.add('quantity');
                form.appendChild(quantityElement);
            }
            quantityElement.innerText = 'In Cart: ' + data.quantity;
            quantityElement.style.display = 'block';

            // Update the cart count dynamically
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.innerText = data.distinctProductCount;
            }

            // Set a timeout to remove the message after 3 seconds
            setTimeout(() => {
                messageElement.innerText = '';
            }, 3000);
        } else {
            alert('Failed to add product to cart.');
        }
    })
    .catch(error => {
        console.error('Error adding product to cart:', error);
    });
}
</script>
</body>
</html>
<?php
$stmtCategory->close();
$stmtProducts->close();
$conn->close();
?>
<style>
        .category-products {
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .category-products h2 {
            font-size: 1.75rem;
            color: #2a9d8f;
            margin-bottom: 20px;
            border-bottom: 3px solid #2a9d8f;
            padding-bottom: 10px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .product-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }


        .product-image-link {
            display: block;
            overflow: hidden;
        }

        .product-image-link img {
            width: 100%;
            height: auto;
        }

        .product-info {
            padding: 15px;
            text-align: center;
        }

        .product-info h3 {
            font-size: 1.2rem;
            color: black;
            margin-bottom: 10px;
        }

        .product-info h3 a {
            text-decoration: none;
            color: #2a9d8f;
            transition: color 0.3s ease;
        }

        .product-info h3 a:hover {
            color: #e76f51;
        }

        .product-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .product-price {
            font-size: 1rem;
            font-weight: bold;
            color: #e76f51;
            margin-bottom: 15px;
        }

        .product-actions {
            padding: 15px;
            background-color: #f9f9f9;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }

        .cart-button {
            background-color: green;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .quantity {
            margin-top: 10px;
            color: #555;
            font-size: 0.85rem;
        }

        .message {
            margin-top: 10px;
            color: #2a9d8f;
            font-size: 0.85rem;
        }
    </style>