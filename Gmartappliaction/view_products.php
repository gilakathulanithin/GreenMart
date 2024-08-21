<?php
session_start();
include 'connectdb.php';  // Include the database connection script

// Check if the user is logging out
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Initialize an empty response array
$response = array();

// Initialize variables to avoid undefined variable notices
$cartUpdated = false;
$limitExceeded = false;

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['product_id']) && isset($_POST['action'])) {
        $productId = intval($_POST['product_id']);
        $action = $_POST['action'];

        // Ensure the cart is initialized
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        $quantity = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0;

        if ($action === 'increment') {
            if ($quantity < 6) {
                $quantity++;
                $_SESSION['cart'][$productId] = $quantity;

                $cartUpdated = true;
            } else {
                $limitExceeded = true;
            }
        } elseif ($action === 'decrement') {
            if ($quantity > 0) {
                $quantity--;
                if ($quantity > 0) {
                    $_SESSION['cart'][$productId] = $quantity;
                } else {
                    unset($_SESSION['cart'][$productId]);
                }

                $cartUpdated = true;
            }
        }

        $newCartCount = getUniqueProductCount();

        if ($cartUpdated) {
            $response['success'] = true;
            $response['uniqueProductCount'] = $newCartCount;
        } elseif ($limitExceeded) {
            $response['error'] = 'limit_exceeded';
            $response['uniqueProductCount'] = $newCartCount; // Provide current count even on error
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if (isset($_POST['fetch_subcategories']) && $_POST['fetch_subcategories'] === 'true') {
        $category = intval($_POST['category']);
        $subcategories = fetchSubcategories($conn, $category);
        echo json_encode($subcategories);
        exit();
    }
}

// Initialize search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : '';
$subcategory = isset($_GET['subcategory']) ? intval($_GET['subcategory']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Clear filters
if (isset($_GET['clearFilters'])) {
    $search = '';
    $category = '';
    $subcategory = '';
    $sort = '';
}

// Function to fetch categories
function fetchCategories($conn) {
    $query = "SELECT id, name FROM categories";
    $result = $conn->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch subcategories
function fetchSubcategories($conn, $category_id) {
    $category_id = intval($category_id);
    $query = "SELECT id, name FROM subcategories WHERE category_id = $category_id";
    $result = $conn->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch products with discounts and images
function fetchProducts($conn, $search, $category, $subcategory, $sort) {
    $search = $conn->real_escape_string($search);

    $query = "SELECT p.id, p.name, p.price, COALESCE(d.discount_percentage, 0) AS discount_percentage, pi.image_path
              FROM products p
              LEFT JOIN discounts d ON p.id = d.product_id
              LEFT JOIN product_images pi ON p.id = pi.product_id
              WHERE 1";

    if ($search) {
        $query .= " AND p.name LIKE '%$search%'";
    }

    if ($category) {
        $query .= " AND p.category_id = $category";
    }

    if ($subcategory) {
        $query .= " AND p.subcategory_id = $subcategory";
    }

    if ($sort === 'low-to-high') {
        $query .= " ORDER BY p.price ASC";
    } elseif ($sort === 'high-to-low') {
        $query .= " ORDER BY p.price DESC";
    }

    $result = $conn->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $products = array();
    while ($row = $result->fetch_assoc()) {
        // Ensure that only the first image is used if there are multiple
        if (!isset($products[$row['id']])) {
            $products[$row['id']] = $row;
        }
    }
    return array_values($products);
}

// Fetch categories and subcategories
$categories = fetchCategories($conn);
$subcategories = $category ? fetchSubcategories($conn, $category) : array();
$products = fetchProducts($conn, $search, $category, $subcategory, $sort);

// Function to count unique products in the cart
function getUniqueProductCount() {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
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

// Get cart count
$cart_count = getUniqueProductCount();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenMart - Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
   body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f5f5;
}

.header, .footer {
    background-color: #ffffff;
    border-bottom: 1px solid #ddd;
}

.header {
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.header .logo img {
    width: 50px;
}

.header .logo h1 {
    display: inline;
    font-size: 24px;
    margin: 0;
    color: #4CAF50;
    vertical-align: middle;
}

.header .search input {
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ddd;
    width: 300px;
}

.header .cart {
    display: flex;
    align-items: center;
}

.header .cart a {
    color: #333;
    text-decoration: none;
    display: flex;
    align-items: center;
}

.header .cart a .fas {
    margin-right: 5px;
}

.nav {
    display: flex;
    justify-content: center;
    background-color: #4CAF50;
}

.nav a {
    padding: 14px 20px;
    display: block;
    color: white;
    text-align: center;
    text-decoration: none;
}

.nav a:hover {
    background-color: #45a049;
}

.main-content {
    display: flex;
    flex-direction: row;
    padding: 20px;
}

.filter-bar {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
}

.filter-bar select, .filter-bar input, .filter-bar button {
    padding: 10px;
    font-size: 14px;
}

.filters {
    flex: 1;
    min-width: 250px;
    background-color: #f4f4f4;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-right: 20px;
}

.filters h2, .filters h3 {
    margin: 0 0 10px;
}

.filters label {
    display: block;
    margin-bottom: 5px;
}

.products {
    flex: 3;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.product-card {
    background-color: #fff;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    width: 100%;
    max-width: 300px;
}

.product-card:hover {
    transform: scale(1.03);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.product-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 4px;
}

.product-card .info {
    padding: 15px;
}

.product-card .info h4 {
    margin: 0 0 10px;
    font-size: 18px;
    color: #333;
}

.product-card .info p {
    margin: 0 0 10px;
    color: #666;
}

.product-card .price {
    font-size: 20px;
    font-weight: bold;
    color: #4CAF50;
}

.product-card .actions {
    padding: 15px;
    text-align: center;
}

.product-card .actions button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.product-card .actions button:hover {
    background-color: #45a049;
}

.footer {
    padding: 10px;
    text-align: center;
}

.footer p {
    margin: 0;
}
.message {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 10px 20px;
    border-radius: 5px;
    color: #fff;
    z-index: 1000;
    opacity: 0.9;
}

.message.success {
    background-color: #28a745; /* Green */
}

.message.error {
    background-color: #dc3545; /* Red */
}
/* Modal styles */
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5); 
}

.modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}


/* Responsive design */
@media (max-width: 768px) {
    .header .search input {
        width: 100%;
    }

    .filter-bar {
        flex-direction: column;
    }

    .products {
        flex-direction: column;
        align-items: center;
    }

    .product-card {
        max-width: 100%;
    }
}

@media (max-width: 480px) {
    .nav a {
        padding: 10px 15px;
        font-size: 14px;
    }

    .product-card {
        width: 100%;
    }
}

    </style>
</head>
<body>
     
<div class="header">
        <div class="logo">
            <img src="https://via.placeholder.com/50x50?text=GM" alt="GreenMart Logo">
            <h1>GreenMart</h1>
        </div>
        <div class="search">
            <input type="text" id="search-bar" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="user-menu">
            <?php if (isset($_SESSION['username'])) : ?>
                <div class="profile-container">
                    <div class="profile-icon" id="profileIcon">  <i class="fas fa-user"></i></div>
                    <div class="profile-dropdown" id="profileDropdown">
                        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                        <a href="?logout=true" class="logout-link" style="text-decoration:none; color:green;font-weight:bolder;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    </div>
                </div>
            <?php else : ?>
                <div class="login-container">
                <a class="login-link" href="login.php" style="text-decoration:none; color:green;font-weight:bolder;">
                    <i class="fas fa-sign-in-alt" ></i> Login
                </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="cart">
        <a href="view_cart.php">
    <i class="fas fa-shopping-cart"></i>
    Cart (<span id="cart-count"><?php echo $cart_count; ?></span>)
</a>

        </div>
    </div>

    <div class="nav">
        <a href="view_products.php"><i class="fas fa-home"></i> Home</a>
        <a href="myorders.php"><i class="fas fa-home"></i> orders</a>
      
    </div>


    <div class="filter-bar">
    <select id="category-select">
        <option value="">Select Category</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <select id="subcategory-select">
        <option value="">Select Subcategory</option>
        <?php foreach ($subcategories as $subcat): ?>
            <option value="<?php echo $subcat['id']; ?>" <?php echo $subcategory == $subcat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($subcat['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <select id="sort-select">
        <option value="">Sort by</option>
        <option value="low-to-high" <?php echo $sort == 'low-to-high' ? 'selected' : ''; ?>>Price: Low to High</option>
        <option value="high-to-low" <?php echo $sort == 'high-to-low' ? 'selected' : ''; ?>>Price: High to Low</option>
    </select>
    <button id="clear-filters">Clear Filters</button>
</div>

<div class="products">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <?php $images = getProductImages($product['id'], $conn); ?>
            <img src="<?php echo !empty($images) ? $images[0] : 'uploads/default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
            <div class="info">
                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                <p class="price">₹<?php echo number_format($product['price'], 2); ?></p>
            </div>
            <div class="actions">
                <button class="add-to-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
    <!-- Product Details Modal -->
    <div id="productDetailModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="productDetailContent">
            <!-- Product details will be loaded here via AJAX -->
        </div>
    </div>
</div>
    

    <div class="footer">
        <p>&copy; 2024 GreenMart. All rights reserved.</p>
    </div>

    <script>
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', () => {
        const productId = button.getAttribute('data-product-id');

        fetch('view_products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'product_id': productId,
                'action': 'increment'
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show "Product added to cart" message
                showMessage('Product added to cart!', 'success');
            } else if (data.error === 'limit_exceeded') {
                // Show "Limit exceeded" message
                showMessage('Limit exceeded! You can only add up to 6 units of this product.', 'error');
                document.getElementById('cart-count').textContent = data.uniqueProductCount;
            }

            // Update the cart count in the header
            document.getElementById('cart-count').textContent = data.uniqueProductCount;
        })
        .catch(error => {
            console.error('Error updating cart:', error);
        });
    });
});
function showMessage(message, type) {
    const messageContainer = document.createElement('div');
    messageContainer.className = `message ${type}`;
    messageContainer.textContent = message;

    document.body.appendChild(messageContainer);

    // Automatically hide the message after 3 seconds
    setTimeout(() => {
        messageContainer.remove();
    }, 3000);
}
document.getElementById('search-bar').addEventListener('input', function() {
    const search = this.value;
    const category = document.getElementById('category-select').value;
    const subcategory = document.getElementById('subcategory-select').value;
    const sort = document.getElementById('sort-select').value;
    const query = `search=${search}&category=${category}&subcategory=${subcategory}&sort=${sort}`;
    window.location.href = `view_products.php?${query}`;
});

document.getElementById('category-select').addEventListener('change', function() {
    const search = document.getElementById('search-bar').value;
    const category = this.value;
    const subcategory = document.getElementById('subcategory-select').value;
    const sort = document.getElementById('sort-select').value;
    const query = `search=${search}&category=${category}&subcategory=${subcategory}&sort=${sort}`;
    window.location.href = `view_products.php?${query}`;
});

document.getElementById('subcategory-select').addEventListener('change', function() {
    const search = document.getElementById('search-bar').value;
    const category = document.getElementById('category-select').value;
    const subcategory = this.value;
    const sort = document.getElementById('sort-select').value;
    const query = `search=${search}&category=${category}&subcategory=${subcategory}&sort=${sort}`;
    window.location.href = `view_products.php?${query}`;
});

document.getElementById('sort-select').addEventListener('change', function() {
    const search = document.getElementById('search-bar').value;
    const category = document.getElementById('category-select').value;
    const subcategory = document.getElementById('subcategory-select').value;
    const sort = this.value;
    const query = `search=${search}&category=${category}&subcategory=${subcategory}&sort=${sort}`;
    window.location.href = `view_products.php?${query}`;
});

document.getElementById('clear-filters').addEventListener('click', function() {
    document.getElementById('search-bar').value = '';
    document.getElementById('category-select').value = '';
    document.getElementById('subcategory-select').value = '';
    document.getElementById('sort-select').value = '';
    window.location.href = 'view_products.php';
});

// image///
document.addEventListener('DOMContentLoaded', function() {
    // Get the modal
    var modal = document.getElementById("productDetailModal");
    
    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];
    
    // When the user clicks on the product image, open the modal and load details
    document.querySelectorAll('.product-card .product-image').forEach(function(image) {
        image.addEventListener('click', function() {
            var productId = this.closest('.product-card').querySelector('.add-to-cart').dataset.productId;

            // Use AJAX to load product details
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_product_details.php?id=' + productId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('productDetailContent').innerHTML = xhr.responseText;
                    modal.style.display = "block";
                }
            };
            xhr.send();
        });
    });

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});
</script>

</body>
</html>
