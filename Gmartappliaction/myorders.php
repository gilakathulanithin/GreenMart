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

// Fetch orders by status for the logged-in user
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : 'all';

$query = "
    SELECT o.id AS order_id, o.order_date, o.total_amount, o.status, o.payment_status, o.created_at,
           s.name, s.mobile_number, s.address, s.city, s.postal_code,
           oi.product_id, oi.quantity, oi.price, p.name AS product_name
    FROM orders o
    JOIN shipping_addresses s ON o.shipping_id = s.address_id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = $user_id";

if ($status != 'all') {
    $query .= " AND o.status = '$status'";
}

$query .= " ORDER BY o.created_at DESC";

$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Initialize an array to store orders and their items
$orders = array();

while ($row = $result->fetch_assoc()) {
    $order_id = $row['order_id'];
    
    if (!isset($orders[$order_id])) {
        // Initialize the order if not already set
        $orders[$order_id] = array(
            'order_date' => $row['order_date'],
            'total_amount' => $row['total_amount'],
            'status' => $row['status'],
            'payment_status' => $row['payment_status'],
            'created_at' => $row['created_at'],
            'shipping' => array(
                'name' => $row['name'],
                'mobile_number' => $row['mobile_number'],
                'address' => $row['address'],
                'city' => $row['city'],
                'postal_code' => $row['postal_code']
            ),
            'items' => array()
        );
    }
    
    // Append the item to the respective order
    $orders[$order_id]['items'][] = array(
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'quantity' => $row['quantity'],
        'price' => $row['price']
    );
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
<main>

<center>
<h1>My Orders</h1>

<div class="filter-section">
    <a href="?status=all" class="<?php echo ($status == 'all') ? 'active' : '' ?>">All Orders</a>
    <a href="?status=Pending" class="<?php echo ($status == 'Pending') ? 'active' : '' ?>">Pending</a>
    <a href="?status=Shipped" class="<?php echo ($status == 'Shipped') ? 'active' : '' ?>">Shipped</a>
    <a href="?status=Delivered" class="<?php echo ($status == 'Delivered') ? 'active' : '' ?>">Delivered</a>
    <a href="?status=Cancelled" class="<?php echo ($status == 'Cancelled') ? 'active' : '' ?>">Cancelled</a>
</div>
</center>
<?php foreach ($orders as $order_id => $order) { ?>
    <?php foreach ($order['items'] as $item) { ?>
        <div class="order-card">
            <div class="order-header-shipping">
                <div class="order-header">
                    <p><strong>Order #<?php echo $order_id; ?></strong></p>
                    <p>Order Date: <?php echo date('d F Y', strtotime($order['order_date'])); ?></p>
                    <p>Status: <?php echo $order['status']; ?></p>
                    <p>Payment Status: <?php echo $order['payment_status']; ?></p>
                    <p>Created At: <?php echo date('d F Y', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="shipping-info">
                    <h4>Shipping Address:</h4>
                    <p><?php echo $order['shipping']['name']; ?></p>
                    <p><?php echo $order['shipping']['mobile_number']; ?></p>
                    <p><?php echo $order['shipping']['address']; ?></p>
                    <p><?php echo $order['shipping']['city']; ?> - <?php echo $order['shipping']['postal_code']; ?></p>
                </div>
            </div>
            <div class="product-info">
                <p>Product: <?php echo $item['product_name']; ?></p>
                <p>Quantity: <?php echo $item['quantity']; ?></p>
                <p>Price: ₹<?php echo number_format($item['price'], 2); ?></p>
               
                <?php if ($order['status'] === 'Pending') : ?>
                    <form action="cancel_order_item.php" method="post" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['product_id']); ?>">
                        <button type="submit" class="cancel-button" onclick="return confirm('Are you sure you want to cancel this item?');">Cancel Item</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php } ?>
<?php } ?>

</main>
<center><a href="cust_dashboard.php" style=" color:green;font-weight:bolder;">back to profile</a></center>
<br><br>
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
    height: 70vh;
    overflow: auto;
}/* General Styles */
/* General Styles */
.center {
    text-align: center;
    margin: 30px 0;
}

/* Heading Style */
.center h1 {
    font-size: 2.5em;
    color: #333;
    margin-bottom: 20px;
    font-weight: bold;
    text-transform: uppercase;
}

/* Filter Section Styles */
.filter-section {
    margin: 20px 0;
}

.filter-section a {
    text-decoration: none;
    color: green; /* Blue color for links */
    padding: 12px 25px;
    border-radius: 6px;
    margin: 0 8px;
    font-size: 1.1em;
    display: inline-block;
    font-weight: bolder;
    transition: background-color 0.3s, color 0.3s, transform 0.2s;
}

.filter-section a.active,
.filter-section a:hover {
    background-color: green; /* Blue background for active and hover state */
    color: #fff;
    transform: scale(1.05); /* Slight scale effect for active and hover state */
}

/* Order Card Styles */
.order-card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin: 20px auto;
    padding: 20px;
    max-width: 900px; /* Adjust max-width for better layout */
}

.order-header-shipping {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.order-header {
    flex: 1;
    margin-right: 20px;
}

.shipping-info {
    flex: 1;
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.product-info {
    margin-top: 20px;
}

.product-info p {
    margin: 8px 0;
    font-size: 1em;
}

/* Button Styles */
.cancel-button {
    background-color: #dc3545; /* Red background for cancel button */
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.3s, transform 0.2s;
    display: inline-block;
}

.cancel-button:hover {
    background-color: #c82333; /* Darker red on hover */
    transform: scale(1.05); /* Slight scale effect on hover */
}

/* Responsive Design */
@media (max-width: 768px) {
    .order-card {
        padding: 15px;
    }

    .order-header-shipping {
        flex-direction: column;
    }

    .order-header {
        margin-right: 0;
        margin-bottom: 15px;
    }

    .shipping-info {
        margin-top: 15px;
    }

    .filter-section a {
        display: block;
        margin: 8px 0;
        font-size: 1em;
    }
}

@media (max-width: 480px) {
    .center h1 {
        font-size: 2em;
    }

    .filter-section a {
        font-size: 0.9em;
        padding: 10px 20px;
    }

    .order-card {
        padding: 10px;
    }

    .product-info p {
        font-size: 0.9em;
    }

    .cancel-button {
        padding: 10px 20px;
        font-size: 0.9em;
    }
}


</style>