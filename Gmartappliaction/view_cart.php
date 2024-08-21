<?php
session_start();
require 'connectdb.php'; // Include your database connection file
// Check if the user is logging out
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: home.php");
    exit();
}

$total_price = 0; // Initialize total price

// Check for removal request
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }
    header("Location: view_cart.php"); // Redirect to avoid form resubmission
    exit();
}

// Check for quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        $product_id = intval($product_id);
        $quantity = intval($quantity);
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = min($quantity, 6); // Limit to 6
        }
    }

    // Calculate the updated total price
    $total_price = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $query = "SELECT price FROM products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $total_price += $row['price'] * $quantity;
        }
        mysqli_stmt_close($stmt);
    }

    // Get cart count
    $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

    echo json_encode(array(
        'success' => true,
        'total_price' => number_format($total_price, 2),
        'cart_count' => $cart_count
    ));
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


    <style>

/* Main Container */
main {
    padding: 20px;
    max-width: 1200px;
  margin: 10%;
}
.cart-header {
    text-align: center;
    margin-bottom: 20px;
}

.cart-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin: 0;
    padding: 10px;
    background-color: #f1f1f1;
    border-radius: 8px;
    display: inline-block;
}
/* Cart Message */
#message {
    display: block;
    padding: 15px;
    margin-bottom: 20px;
    background-color: #dff0d8;
    color: #3c763d;
    border: 1px solid #d6e9c6;
    border-radius: 8px;
    text-align: center;
    font-size: 16px;
    font-weight: 500;
}
/* Message Box Styling */
.message-box {
    display: none; /* Hidden by default, can be toggled via JS */
    padding: 15px;
    margin-bottom: 20px;
    background-color: #dff0d8; /* Light green background */
    color: #3c763d; /* Dark green text */
    border: 1px solid #d6e9c6; /* Slightly darker border */
    border-radius: 8px; /* Rounded corners */
    font-size: 16px; /* Slightly larger font for readability */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
    transition: all 0.3s ease; /* Smooth transition for showing/hiding */
    max-width: 600px; /* Max width to prevent stretching on large screens */
    margin-left: auto;
    margin-right: auto;
    text-align: center; /* Center the message text */
}

/* Additional Styling for Different Message Types (Optional) */
.message-box.success {
    background-color: #dff0d8;
    color: #3c763d;
    border-color: #d6e9c6;
}

.message-box.error {
    background-color: #f2dede;
    color: #a94442;
    border-color: #ebccd1;
}
/* Cart Form */
#cart-form {
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
}

/* Cart Items */
.cart-items {
    margin-bottom: 30px;
}

.cart-item {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
    padding: 15px 0;
    margin-bottom: 15px;
}

.cart-item img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 20px;
}

.cart-item-details {
    flex: 1;
}

.cart-item-details h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.cart-item-details p {
    margin: 5px 0;
    font-size: 16px;
    color: #555;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.quantity-controls button {
    background-color:green;
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bolder;
}

.quantity-controls button:hover {
    background-color: #0056b3;
}

.quantity-input {
    width: 60px;
    text-align: center;
    padding: 6px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.remove-item {
    color: #dc3545;
    font-size: 22px;
    text-decoration: none;
}

.remove-item:hover {
    color: #c82333;
}

.remove-item .fas {
    font-size: 22px;
}

/* Checkout Section */
.checkout {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;

}

.checkout h3 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.checkout p {
    margin: 5px 0;
    font-size: 18px;
    color: #555;
}

.checkout button {
    background-color: green;
    color: #fff;
    border: none;
    padding: 12px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 18px;
    margin-top: 15px;
    font-weight: bolder;
}



/* Responsive Design */
@media (max-width: 768px) {
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .cart-item img {
        margin-bottom: 10px;
    }

    .quantity-controls {
        margin-bottom: 10px;
    }
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
<div class="main-content">
    <section class="cart-header">
        <h2>Cart</h2>
    </section>
    <div id="message" class="message-box" style="display: none;">
    <!-- Message will be displayed here -->
</div>


    <form id="cart-form" action="view_cart.php" method="POST">
        <div class="cart-items">
        <?php
// Initialize total variables
$total_price = 0;
$sgst = 0;
$cgst = 0;
$shipping_charges = 0;

// Calculate total price
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $query = "SELECT * FROM products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);

        if (!$stmt) {
            die('Query preparation failed: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $item_total = $row['price'] * $quantity; // Calculate item total
            $total_price += $item_total; // Add to cart total price

            // Fetch product images
            $image_query = "SELECT image_path FROM product_images WHERE product_id = ?";
            $stmt_images = mysqli_prepare($conn, $image_query);

            if (!$stmt_images) {
                die('Image query preparation failed: ' . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt_images, "i", $product_id);
            mysqli_stmt_execute($stmt_images);
            $result_images = mysqli_stmt_get_result($stmt_images);

            $image_paths = array();
            if ($result_images->num_rows > 0) {
                while ($image = mysqli_fetch_assoc($result_images)) {
                    $image_paths[] = 'uploads/' . basename($image['image_path']);
                }
            } else {
                $image_paths[] = 'https://via.placeholder.com/80x80?text=No+Image';
            }

            echo '
            <div class="cart-item" data-id="' . $product_id . '">
                <img src="' . htmlspecialchars($image_paths[0]) . '" alt="Product Image">
                <div class="cart-item-details">
                    <h4>' . htmlspecialchars($row['name']) . '</h4>
                    <p>Price: ₹<span class="item-price" data-price="' . $row['price'] . '">' . number_format($row['price'], 2) . '</span></p>
                    <p>Quantity: 
                        <div class="quantity-controls">
                            <button type="button" class="quantity-decrease" data-id="' . $product_id . '">-</button>
                            <input type="number" name="quantities[' . $product_id . ']" value="' . $quantity . '" class="quantity-input" data-price="' . $row['price'] . '" min="1" max="6">
                            <button type="button" class="quantity-increase" data-id="' . $product_id . '">+</button>
                        </div>
                    </p>
                    <p class="item-total">Total: ₹ <span class="item-total-price">' . number_format($item_total, 2) . '</span></p>
                </div>
                <a href="view_cart.php?remove=' . intval($product_id) . '" class="remove-item"><i class="fas fa-trash"></i></a>
            </div>';
        } else {
            echo '<p>Product not found.</p>';
        }

        mysqli_stmt_close($stmt);
        mysqli_stmt_close($stmt_images);
    }

    // Calculate SGST, CGST, and shipping charges
    $sgst = $total_price * 0.09; // 9% SGST
    $cgst = $total_price * 0.09; // 9% CGST
    $shipping_charges = $total_price > 1000 ? 0 : 50; // Free shipping for orders above 1000, otherwise 50

    // Calculate final total
    $final_total = $total_price + $sgst + $cgst + $shipping_charges;

    // Output totals
    echo '
    <div class="checkout">
        <h3>Total Price: ₹<span id="total-price">' . number_format($total_price, 2) . '</span></h3>
        <p>SGST (9%): ₹<span id="sgst">' . number_format($sgst, 2) . '</span></p>
        <p>CGST (9%): ₹<span id="cgst">' . number_format($cgst, 2) . '</span></p>
        <p>Shipping Charges: ₹<span id="shipping-charges">' . number_format($shipping_charges, 2) . '</span></p>
        <h3>Final Total: ₹<span id="final-total">' . number_format($final_total, 2) . '</span></h3>
        <button type="button" id="proceed-checkout">Proceed to Checkout</button>
    </div>';
} else {
    echo '<p>Your cart is empty.</p>';
}
?>

</div>
    </form>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    function toggleProfileMenu() {
        var menu = document.getElementById('profile-menu');
        menu.classList.toggle('show');
    }
        
        function toggleMenu() {
            const nav = document.querySelector('.nav');
            nav.classList.toggle('active');
        }
       
        
    $(document).ready(function() {
        // Handle quantity increase and decrease
        $('.quantity-increase').click(function() {
            var input = $(this).siblings('.quantity-input');
            var value = parseInt(input.val());
            var max = parseInt(input.attr('max'));

            if (value < max) {
                input.val(value + 1).trigger('change');
            }
        });

        $('.quantity-decrease').click(function() {
            var input = $(this).siblings('.quantity-input');
            var value = parseInt(input.val());
            var min = parseInt(input.attr('min'));

            if (value > min) {
                input.val(value - 1).trigger('change');
            }
        });

        // Handle input change for quantity
        $('.quantity-input').change(function() {
            var input = $(this);
            var quantity = parseInt(input.val());
            var price = parseFloat(input.data('price'));

            // Update individual item total
            var item_total = quantity * price;
            input.closest('.cart-item').find('.item-total-price').text(item_total.toFixed(2));

            // Update cart total price
            var total_price = 0;
            $('.item-total-price').each(function() {
                total_price += parseFloat($(this).text());
            });

            // Calculate SGST, CGST, and shipping charges
            var sgst = total_price * 0.09;
            var cgst = total_price * 0.09;
            var shipping_charges = total_price > 1000 ? 0 : 50;
            var final_total = total_price + sgst + cgst + shipping_charges;

            // Update totals on the page
            $('#total-price').text(total_price.toFixed(2));
            $('#sgst').text(sgst.toFixed(2));
            $('#cgst').text(cgst.toFixed(2));
            $('#shipping-charges').text(shipping_charges.toFixed(2));
            $('#final-total').text(final_total.toFixed(2));

            // Send AJAX request to update cart in backend
            $.ajax({
                url: 'view_cart.php',
                method: 'POST',
                data: $('#cart-form').serialize(),
                dataType: 'json',
                success: function(response) {
                    var messageContainer = $('#message');

                    if (response.success) {
                        // Display success message
                        messageContainer.text('Cart has been updated.').show();

                        // Update cart count if needed
                        $('#cart-count').text(response.cart_count);
                    } else if (response.error) {
                        // Display error message
                        messageContainer.text('There was an error updating the cart. Please try again.').show();
                    }

                    // Automatically hide the message after 3 seconds
                    setTimeout(() => {
                        messageContainer.fadeOut(300); // Optional: Fade out effect
                    }, 3000);
                },
                error: function(xhr, status, error) {
                    console.log('AJAX error:', status, error); // Debugging line
                }
            });
        });

        // Proceed to checkout
        $('#proceed-checkout').click(function(e) {
            e.preventDefault(); // Prevent default form submission
            var cartCount = parseInt($('#cart-count').text());

            if (cartCount === 0) {
                alert('Please add products to your cart before proceeding to checkout.');
                window.location.href = 'view_products.php'; // Redirect to view products page
            } else {
                window.location.href = 'payment_shipping.php'; // Proceed to checkout
            }
        });
    });
</script>




</body>
</html>

