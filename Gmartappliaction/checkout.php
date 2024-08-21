<?php
session_start();
require 'connectdb.php'; // Include your database connection file

// Ensure user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    die("User not logged in.");
}

// Fetch existing addresses for the logged-in user
$stmt = $conn->prepare("SELECT address_id, name, mobile_number, address, city, postal_code FROM shipping_addresses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$existing_addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Process form submission
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart_items = $_SESSION['cart'];

    // Dynamically get values from POST request
    $shipping_id = isset($_POST['shipping_id']) ? $_POST['shipping_id'] : null;
    $new_address = isset($_POST['new_address']) ? $_POST['new_address'] : null;
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $mobile_number = isset($_POST['mobile_number']) ? $_POST['mobile_number'] : null;
    $city = isset($_POST['city']) ? $_POST['city'] : null;
    $postal_code = isset($_POST['postal_code']) ? $_POST['postal_code'] : null;
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Credit Card';
    $payment_reference = isset($_POST['payment_reference']) ? $_POST['payment_reference'] : null;

    // Insert the new address into the shipping_addresses table if provided
    if ($new_address && $name && $mobile_number && $city && $postal_code) {
        $stmt = $conn->prepare("INSERT INTO shipping_addresses (user_id, address, name, mobile_number, city, postal_code) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $new_address, $name, $mobile_number, $city, $postal_code);
        $stmt->execute();
        $shipping_id = $stmt->insert_id; // Get the ID of the new address
    } elseif (!$shipping_id && empty($existing_addresses)) {
        die("No shipping address provided.");
    } elseif (!$shipping_id) {
        die("No shipping address selected.");
    }

    // Calculate total amount
    $total_amount = 0;
    $order_items = array();
    foreach ($cart_items as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if ($product) {
            $total_amount += $product['price'] * $quantity;
            $order_items[] = array(
                'name' => $product['name'],
                'quantity' => $quantity,
                'price' => $product['price']
            );
        }
    }

    // Prepare values for binding
    $order_date = date('Y-m-d');

    // Insert order into the database
    $sql_order = "INSERT INTO orders (user_id, total_amount, order_date, shipping_id, payment_method, payment_reference) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("idsiss", $user_id, $total_amount, $order_date, $shipping_id, $payment_method, $payment_reference);
    $stmt_order->execute();

    if ($stmt_order->error) {
        die("Error executing order statement: " . htmlspecialchars($stmt_order->error));
    }

    $order_id = $stmt_order->insert_id;

    // Insert order details into the database
    $sql_order_detail = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    foreach ($cart_items as $product_id => $quantity) {
        // Retrieve product price
        $stmt_price = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt_price->bind_param("i", $product_id);
        $stmt_price->execute();
        $result_price = $stmt_price->get_result();
        $product_data = $result_price->fetch_assoc();
        $product_price = $product_data['price'];

        // Insert order item
        $stmt_order_detail = $conn->prepare($sql_order_detail);
        $stmt_order_detail->bind_param("iids", $order_id, $product_id, $quantity, $product_price);
        $stmt_order_detail->execute();
        
        if ($stmt_order_detail->error) {
            die("Error executing order detail statement: " . htmlspecialchars($stmt_order_detail->error));
        }
    }

    // Update the order with payment status
    $sql_update_order = "UPDATE orders SET payment_status = ?, payment_reference = ? WHERE id = ?";
    $stmt_update_order = $conn->prepare($sql_update_order);
    $payment_status = 'Paid'; // Replace with actual payment status if different
    $stmt_update_order->bind_param("ssi", $payment_status, $payment_reference, $order_id);
    $stmt_update_order->execute();

    if ($stmt_update_order->error) {
        die("Error executing update statement: " . htmlspecialchars($stmt_update_order->error));
    }

    // Clear the cart
    unset($_SESSION['cart']);

    // Retrieve order details for display
    $stmt_order_details = $conn->prepare("SELECT payment_method, payment_reference FROM orders WHERE id = ?");
    $stmt_order_details->bind_param("i", $order_id);
    $stmt_order_details->execute();
    $order_details_result = $stmt_order_details->get_result();
    $order_details = $order_details_result->fetch_assoc();
    $stmt_order_details->close();

    // Check if the user has already rated the website
    $stmt_check_rating = $conn->prepare("SELECT COUNT(*) FROM website_rating WHERE user_id = ?");
    $stmt_check_rating->bind_param("i", $user_id);
    $stmt_check_rating->execute();
    $stmt_check_rating->bind_result($rating_count);
    $stmt_check_rating->fetch();
    $stmt_check_rating->close();

    // Show rating popup if user hasn't rated yet
    $_SESSION['show_rating_popup'] = ($rating_count == 0);
    // Count total distinct products in cart
$totalCartItems = count($_SESSION['cart'] ?? []);

    // Display order confirmation with rating popup
    ?>
  <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Order Confirmation</title>
</head>
<body>
       
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #e6f4e6; /* Very light green */
                color: #333;
                text-align: center;
                padding: 0px;
            }
            h1 {
                color: #3c763d; /* Dark green */
            }
            table {
                width: 80%;
                margin: 20px auto;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            #rating-popup {
                display: none;
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                justify-content: center;
                align-items: center;
            }
            .popup-content {
                background: white;
                padding: 20px;
                border-radius: 5px;
                width: 300px;
                text-align: center;
            }
            .stars {
                display: flex;
                justify-content: center;
                gap: 10px;
                margin-bottom: 20px;
            }
            .stars input[type='radio'] {
                display: none;
            }
            .stars label {
                font-size: 30px;
                cursor: pointer;
            }
            .stars label:hover, .stars label:hover ~ label {
                color: #FFD700;
            }
            .stars input[type='radio']:checked ~ label {
                color: #FFD700;
            }
        </style>
        <script>
            function showRatingPopup() {
                document.getElementById('rating-popup').style.display = 'flex';
            }

            function closeRatingPopup() {
                document.getElementById('rating-popup').style.display = 'none';
            }

            function submitRating() {
                var form = document.getElementById('rating-form');
                var formData = new FormData(form);

                fetch('submit_rating.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                .then(data => {
                    closeRatingPopup();
                    alert('Thank you for your feedback!');
                }).catch(error => {
                    console.error('Error:', error);
                });

                return false;
            }

            window.onload = function() {
                <?php if (isset($_SESSION['show_rating_popup']) && $_SESSION['show_rating_popup']): ?>
                    showRatingPopup();
                    <?php unset($_SESSION['show_rating_popup']); ?>
                <?php endif; ?>
            };
        </script>
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
       <main>
       <h1>Order Confirmation</h1>
        <p>Thank you for your order! Your order has been placed successfully.</p>
        <p>Order ID: <?php echo htmlspecialchars($order_id); ?></p>
        <p>Payment Method: <?php echo htmlspecialchars($order_details['payment_method']); ?></p>
        <p>Payment Reference: <?php echo htmlspecialchars($order_details['payment_reference']); ?></p>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
            <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($item['price']); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2"><strong>Total Amount</strong></td>
                <td><strong><?php echo htmlspecialchars($total_amount); ?></strong></td>
            </tr>
        </table>
        <p><a href='product.php'>Return to Homepage</a></p>
        <div id="rating-popup">
            <div class="popup-content">
                <h2>Rate Our Website</h2>
                <form id="rating-form" onsubmit="return submitRating();">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                    <div class="stars">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5">&#9733;</label>
                        <input type="radio" id="star4" name="rating" value="4" required>
                        <label for="star4">&#9733;</label>
                        <input type="radio" id="star3" name="rating" value="3" required>
                        <label for="star3">&#9733;</label>
                        <input type="radio" id="star2" name="rating" value="2" required>
                        <label for="star2">&#9733;</label>
                        <input type="radio" id="star1" name="rating" value="1" required>
                        <label for="star1">&#9733;</label>
                    </div>
                    <textarea name="comment" placeholder="Leave a comment..." rows="4" style="width: 100%;"required></textarea><br><br>
                    <button type="submit">Submit Rating</button>
                    <button onclick="closeRatingPopup();">Close</button>
                </form>
            </div>
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
    </body>
    </html>
<?php
} else {
    echo "<p>Your cart is empty.</p>";
}

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO website_rating (user_id, rating, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $rating, $comment);
        if ($stmt->execute()) {
            $_SESSION['show_rating_popup'] = false;
        } else {
            echo "Error inserting rating: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}
?>
