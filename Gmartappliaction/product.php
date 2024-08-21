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

// Get categories and subcategories for filtering
function getCategories($conn) {
    $query = "SELECT * FROM categories";
    $result = $conn->query($query);
    $categories = array();
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}

function getSubcategories($categoryId, $conn) {
    $query = "SELECT * FROM subcategories WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subcategories = array();
    
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
    
    $stmt->close();
    return $subcategories;
}

// Initialize filter and sorting variables
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : null;
$subcategoryFilter = isset($_GET['subcategory']) ? intval($_GET['subcategory']) : null;
$searchQuery = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc'; // Default sorting

// Determine the sorting criteria
switch ($sort) {
    case 'price_asc':
        $orderBy = 'price ASC';
        break;
    case 'price_desc':
        $orderBy = 'price DESC';
        break;
    case 'name_desc':
        $orderBy = 'name DESC';
        break;
    case 'name_asc':
    default:
        $orderBy = 'name ASC';
        break;
}

// Build the WHERE clause based on filters
$whereClauses = array();
if ($categoryFilter) {
    $whereClauses[] = "category_id = $categoryFilter";
}
if ($subcategoryFilter) {
    $whereClauses[] = "subcategory_id = $subcategoryFilter";
}
if ($searchQuery) {
    $whereClauses[] = "(name LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%')";
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Fetch all products from the database with filters and sorting
$sqlProducts = "SELECT * FROM products $whereSql ORDER BY $orderBy";
$resultProducts = $conn->query($sqlProducts);

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
    <title>All Products - GreenMart</title>
    <style>
/* General Layout */
main {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 30%;
    background-color: #f4f4f4;
    padding: 20px;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
}

.all-products {
    width: 70%;
    padding: 20px;
    background-color: #fff;
}

.filter-form, .sorting-form {
    margin-bottom: 20px;
}

.filter-form h2, .sorting-form h2 {
    font-size: 1.2em;
    margin-bottom: 10px;
}

.filter-form label, .sorting-form label {
    display: block;
    margin-bottom: 5px;
}

.filter-form select, .sorting-form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.clear-filters {
    display: inline-block;
    margin-top: 10px;
    font-weight: bold;
    color: black;
    text-decoration: none;
}

.clear-filters:hover {
    
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.product-card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    text-align: center;
    height: 450px; /* Fixed height to ensure uniformity */
    position: relative; /* To position message and cart count properly */
}

.product-card img {
    width: 100%;
    height: 200px; /* Fixed height for images */
    object-fit: cover;
    border-bottom: 1px solid #ddd;
    margin-bottom: 10px;
}

.product-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
}

.product-info h3 {
    font-size: 1.1em;
    margin: 10px 0;
 
}

.product-info p {
    color: black;
    margin: 5px 0;
    font-weight: bold;
}

.product-info .cart-button {
    background-color: #28a745;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    margin-top: 10px;
}

.product-info .cart-button:hover {
    background-color: #218838;
}

.product-info .quantity {
    margin: 5px 0;
    font-size: 0.9em;
    display: flex;
    align-items: center;
}

.product-info .quantity::before {
    content: url('icon-cart.svg'); /* Replace with the path to your cart icon */
    margin-right: 5px;
}

.product-info .message {
    margin: 10px 0;
    color: #007bff;
    font-size: 0.9em;
    display: flex;
    align-items: center;
}

.product-info .message::before {
    content: url('icon-message.svg'); /* Replace with the path to your message icon */
    margin-right: 5px;
}

.product-info a {
    text-decoration: none;
    color: black;
}

/* .product-info a:hover {
    text-decoration: underline;
} */

/* Responsive Design */
@media (max-width: 1200px) {
    .sidebar {
        width: 25%;
    }

    .all-products {
        width: 75%;
    }
}

@media (max-width: 992px) {
    .sidebar {
        width: 30%;
    }

    .all-products {
        width: 70%;
    }
}

@media (max-width: 768px) {
    main {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        order: 2;
    }

    .all-products {
        width: 100%;
        order: 1;
    }
}

@media (max-width: 576px) {
    .product-card {
        padding: 10px;
    }

    .product-info h3 {
        font-size: 1em;
    }

    .product-info p {
        font-size: 0.9em;
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
           <!-- <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search...">
                    <button class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div> -->
                <form method="GET" action="product.php">
            <div class="search-container">
                
                    <input type="text" class="search-input" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                 </div>
            </form>

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
                                 <a href="logout.php" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
    <aside class="sidebar">
        <!-- Filtering Form -->
        <section class="filter-form">
            <h2>Filters</h2>
            <form method="get" action="">
                <label for="category">Category:</label>
                <select name="category" id="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php
                    $categories = getCategories($conn);
                    foreach ($categories as $category) {
                        echo '<option value="' . $category['id'] . '"' . ($category['id'] == $categoryFilter ? ' selected' : '') . '>' . htmlspecialchars($category['name']) . '</option>';
                    }
                    ?>
                </select>
                
                <?php if ($categoryFilter): ?>
                    <label for="subcategory">Subcategory:</label>
                    <select name="subcategory" id="subcategory" onchange="this.form.submit()">
                        <option value="">All Subcategories</option>
                        <?php
                        $subcategories = getSubcategories($categoryFilter, $conn);
                        foreach ($subcategories as $subcategory) {
                            echo '<option value="' . $subcategory['id'] . '"' . ($subcategory['id'] == $subcategoryFilter ? ' selected' : '') . '>' . htmlspecialchars($subcategory['name']) . '</option>';
                        }
                        ?>
                    </select>
                <?php endif; ?>

                <a href="product.php" class="clear-filters">Clear Filters</a>
            </form>
        </section>

        <!-- Sorting Form -->
        <section class="sorting-form">
            <h2>Sort By</h2>
            <form method="get" action="">
                <label for="sort">Sort By:</label>
                <select name="sort" id="sort" onchange="this.form.submit()">
                    <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                    <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                    <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                </select>
            </form>
        </section>
    </aside>
 
    <section class="all-products">
    <h1>All Products</h1>
    <div class="products-grid">
        <!-- Product Cards -->
        <?php if ($resultProducts->num_rows > 0): ?>
            <?php while ($product = $resultProducts->fetch_assoc()): ?>
                <?php 
                $images = getProductImages($product['id'], $conn); 
                $cartQuantity = getCartQuantity($product['id']);
                ?>
                <div class="product-card">
                    <a href="thatpage.php?id=<?php echo $product['id']; ?>">
                        <img src="<?php echo !empty($images) ? htmlspecialchars($images[0]) : 'https://via.placeholder.com/100'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </a>
                    <div class="product-info">
                        <h3><a href="thatpage.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                        <!-- <p><?php echo htmlspecialchars($product['description']); ?></p> -->
                        <p><strong>Price:</strong> ₹<?php echo htmlspecialchars($product['price']); ?></p>
                        <!-- Add to Cart Form -->
                        <form id="add-to-cart-form-<?php echo $product['id']; ?>" onsubmit="addToCart(event, <?php echo $product['id']; ?>)">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                            <button class="cart-button" type="submit">Add to Cart</button>
                            <?php if ($cartQuantity > 0): ?>
                                <p class="quantity" id="quantity-<?php echo $product['id']; ?>">  In Cart: <?php echo $cartQuantity; ?></p>
                            <?php endif; ?>
                            <p class="message" id="message-<?php echo $product['id']; ?>"></p>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No products available.</p>
        <?php endif; ?>
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
</script>


