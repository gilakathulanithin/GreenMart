<?php
session_start();
require 'connectdb.php'; // Include your database connection file

// Ensure user is logged in and user_id is available
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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_id = isset($_POST['shipping_id']) ? $_POST['shipping_id'] : null;
    $new_address = isset($_POST['new_address']) ? $_POST['new_address'] : null;
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $mobile_number = isset($_POST['mobile_number']) ? $_POST['mobile_number'] : null;
    $city = isset($_POST['city']) ? $_POST['city'] : null;
    $postal_code = isset($_POST['postal_code']) ? $_POST['postal_code'] : null;
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Credit Card';
    $payment_reference = isset($_POST['payment_reference']) ? $_POST['payment_reference'] : null;

    // Validate address selection
    if (!$shipping_id && (!$new_address || !$name || !$mobile_number || !$city || !$postal_code)) {
        die("Please provide a shipping address.");
    }

    // Insert the new address into the shipping_addresses table if provided
    if ($new_address && $name && $mobile_number && $city && $postal_code) {
        $stmt = $conn->prepare("INSERT INTO shipping_addresses (user_id, address, name, mobile_number, city, postal_code) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $new_address, $name, $mobile_number, $city, $postal_code);
        $stmt->execute();
        $shipping_id = $stmt->insert_id; // Get the ID of the new address
    }

    // Further processing (e.g., order placement) would go here
}
// Count total distinct products in cart
$totalCartItems = count($_SESSION['cart'] ?? []);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>

#shipping_id {
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 10px;
    font-size: 16px;
}

#shipping_id option {
    white-space: normal;
    word-wrap: break-word;
    padding: 10px;
    font-size: 16px;
}



#checkout-form {
    max-width: 600px;
    margin: 40px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

#checkout-form fieldset {
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 10px;
}

#checkout-form legend {
    font-weight: bold;
    margin-bottom: 10px;
}

#checkout-form label {
    display: block;
    margin-bottom: 5px;
}

#checkout-form input[type="text"],
#checkout-form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

#checkout-form input[type="button"] {
    background-color: #4CAF50;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

#checkout-form input[type="button"]:hover {
    background-color: #3e8e41;
}

#newAddressForm {
    display: none;
}

#credit-card-fields,
#upi-fields {
    margin-top: 10px;
}

#confirmationModal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}

#confirmationModal .modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
}

#confirmationModal .close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

#confirmationModal .close:hover,
#confirmationModal .close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}


main {
    font-family: Arial, sans-serif;
}




.checkout-form-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.address-fieldset {
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 10px;
}

.payment-fieldset {
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 10px;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-input {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

.form-button {
    background-color: #4CAF50;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

.form-button:hover {
    background-color: #3e8e41;
}

.shipping-select {
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 10px;
    font-size: 16px;
}

.shipping-option {
    white-space: normal;
    word-wrap: break-word;
    padding: 10px;
    font-size: 16px;
}

.new-address-form {
    display: none;
}

.credit-card-fields {
    margin-top: 10px;
}

.upi-fields {
    margin-top: 10px;
}

.confirmation-modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;



    .checkout-form-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.address-fieldset {
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 10px;
}

.payment-fieldset {
    margin-bottom: 20px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 10px;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-input {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

.form-button {
    background-color: #4CAF50;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

.shipping-select {
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 10px;
    font-size: 16px;
}




/* RWD */
@media (max-width: 768px) {
    .checkout-form-container {
        padding: 10px;
    }
}

@media (max-width: 480px) {
    .form-input {
        padding: 5px;
    }
    .form-button {
        padding: 5px 10px;
    }
    .shipping-select {
        padding: 5px;
    }
    .confirmation-modal-content {
        padding: 10px;
    }
}

    border: 1px solid #888;
    width: 80%;
}

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
    justify-content: center;
    align-items: center;
    padding: 20px;
    box-sizing: border-box;
}

.modal-content {
    background-color: #fff;
    margin: auto;
    padding: 20px;
    border-radius: 8px;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
}

.modal-content p {
    margin: 20px 0;
    font-size: 1.2em;
    color: #333;
}

.modal-content .close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

/* .modal-content .close:hover,
.modal-content .close:focus {
    color: #000;
} */

.modal-content button {
    background-color: green;
    color: white;
    border: none;
    padding: 10px 20px;
    margin: 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.3s;
}



@media (max-width: 768px) {
    .modal-content {
        padding: 15px;
        max-width: 90%;
    }

    .modal-content p {
        font-size: 1.1em;
    }

    .modal-content button {
        padding: 8px 16px;
        font-size: 0.9em;
    }
}

@media (max-width: 480px) {
    .modal-content {
        padding: 10px;
        max-width: 100%;
    }

    .modal-content p {
        font-size: 1em;
    }

    .modal-content button {
        padding: 6px 12px;
        font-size: 0.8em;
        margin: 5px;
    }
}






/* Container CSS */
#credit-card-fields {
    background-color: #f9f9f9;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 400px;
}

#upi-fields {
    background-color: #f9f9f9;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 400px;
}

/* Card Number CSS */
#card_number {
    width: 270px;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #2e8b57;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
    font-family: monospace;
}

#card_number:focus {
    border-color: #1c6f39;
}

/* Expiry Date CSS */
#expiry_date {
    width: 100px;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #2e8b57;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
}

#expiry_date:focus {
    border-color: #1c6f39;
}

/* CVV CSS */
#cvv {
    width: 80px;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #2e8b57;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
}

#cvv:focus {
    border-color: #1c6f39;
}

/* UPI ID CSS */
#upi_id {
    width: 300px;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #2e8b57;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
}

#upi_id:focus {
    border-color: #1c6f39;
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
 <h1>Checkout</h1>
 </center>
    <form id="checkout-form" action="checkout.php" method="post">
        <!-- Existing Addresses -->
        <fieldset>
            <legend>Existing Addresses</legend>
            <label for="shipping_id">Select Address:</label>
            <select id="shipping_id" name="shipping_id" onchange="toggleAddressForm()">
                <option value="">--Select an Address--</option>
                <?php
                if (!empty($existing_addresses)) {
                    foreach ($existing_addresses as $address) {
                        echo "<option value='" . htmlspecialchars($address['address_id']) . "'>"
                            . htmlspecialchars($address['address']) . ", "
                            . htmlspecialchars($address['name']) . ", "
                            . htmlspecialchars($address['mobile_number']) . ", "
                            . htmlspecialchars($address['city']) . ", "
                            . htmlspecialchars($address['postal_code']) . "</option>";
                    }
                } else {
                    echo "<option value=''>No existing addresses available</option>";
                }
                ?>
            </select>
            <button type="button" onclick="showNewAddressForm()">Add New Address</button>
        </fieldset>

        <!-- New Address Form -->
        <div id="newAddressForm" class="form-container">
            <fieldset>
                <legend>New Address</legend>
                <label for="new_address">Address:</label>
                <input type="text" id="new_address" name="new_address"><br>

                <label for="name">Name:</label>
                <input type="text" id="name" name="name"><br>

                <label for="mobile_number">Mobile Number:</label>
                <input type="text" id="mobile_number" name="mobile_number"><br>

                <label for="city">City:</label>
                <input type="text" id="city" name="city"><br>

                <label for="postal_code">Postal Code:</label>
                <input type="text" id="postal_code" name="postal_code"><br>
            </fieldset>
        </div>

        <!-- Payment Details -->

      

<fieldset>
    <legend>Payment Details</legend>
    <label for="payment_method">Payment Method:</label>
    <select id="payment_method" name="payment_method" required onchange="showPaymentFields()">
        <option value="">Please select payment mode</option>
        <option value="Credit Card">Credit Card</option>
        <option value="UPI">UPI</option>
    </select>
    <div id="credit-card-fields" style="display: none;">
        <label for="card_number">Card Number:</label>
        <input type="text" id="card_number" name="card_number" placeholder="XXXX-XXXX-XXXX-XXXX">
        <span id="card_number_error" style="color: red;"></span>
        <label for="expiry_date">Expiry Date:</label>
        <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
        <span id="expiry_date_error" style="color: red;"></span>
        <label for="cvv">CVV:</label>
        <input type="text" id="cvv" name="cvv" placeholder="XXX">
        <span id="cvv_error" style="color: red;"></span>
    </div>
    <div id="upi-fields" style="display: none;">
        <label for="upi_id">UPI ID:</label>
        <input type="text" id="upi_id" name="upi_id" placeholder="yourid@upi">
        <span id="upi_id_error" style="color: red;"></span>
    </div>
    <input type="hidden" id="payment_reference" name="payment_reference" value="">
</fieldset>



        <input type="button" value="Proceed to Checkout" onclick="showConfirmationPopup()">
    </form>

    <!-- The Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p>Are you sure you want to proceed with the checkout?</p>
            <button onclick="confirmCheckout()">Yes</button>
            <button onclick="closeModal()">No</button>
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
    <script>
        function generatePaymentReference() {
            const prefix = 'REF-';
            const timestamp = Date.now(); // Get current timestamp
            const randomPart = Math.random().toString(36).substring(2, 8).toUpperCase(); // Generate random part
            return prefix + timestamp + '-' + randomPart;
        }

        function setPaymentReference() {
            const paymentReferenceField = document.getElementById('payment_reference');
            paymentReferenceField.value = generatePaymentReference();
        }

        window.onload = function() {
            setPaymentReference();
        }

        function showNewAddressForm() {
            document.getElementById('newAddressForm').style.display = 'block';
        }

        function toggleAddressForm() {
            const select = document.getElementById('shipping_id');
            const newAddressForm = document.getElementById('newAddressForm');
            if (select.value === '') {
                newAddressForm.style.display = 'block';
            } else {
                newAddressForm.style.display = 'none';
            }
        }

        function showConfirmationPopup() {
    const shippingId = document.getElementById('shipping_id').value;
    const newAddressFields = document.getElementById('newAddressForm').style.display === 'block';
    const paymentMethod = document.getElementById('payment_method').value;

    console.log('Shipping ID:', shippingId);
    console.log('New Address Fields Visible:', newAddressFields);
    console.log('Payment Method:', paymentMethod);

    if (!shippingId && !newAddressFields) {
        alert('Please select an existing address or add a new address.');
        return;
    }

    if (!paymentMethod) {
        alert('Please select a payment method.');
        return;
    }

    let isValid = true;
    let errorMessage = '';

    if (paymentMethod === 'Credit Card') {
        const cardNumber = document.getElementById('card_number').value.trim();
        const expiryDate = document.getElementById('expiry_date').value.trim();
        const cvv = document.getElementById('cvv').value.trim();

        console.log('Card Number:', cardNumber);
        console.log('Expiry Date:', expiryDate);
        console.log('CVV:', cvv);

        if (!cardNumber) {
            errorMessage = 'Card Number is required.';
            document.getElementById('card_number_error').textContent = errorMessage;
            isValid = false;
        } else {
            document.getElementById('card_number_error').textContent = '';
        }

        if (!expiryDate) {
            errorMessage = 'Expiry Date is required.';
            document.getElementById('expiry_date_error').textContent = errorMessage;
            isValid = false;
        } else {
            document.getElementById('expiry_date_error').textContent = '';
        }

        if (!cvv) {
            errorMessage = 'CVV is required.';
            document.getElementById('cvv_error').textContent = errorMessage;
            isValid = false;
        } else {
            document.getElementById('cvv_error').textContent = '';
        }
    } else if (paymentMethod === 'UPI') {
        const upiId = document.getElementById('upi_id').value.trim();

        console.log('UPI ID:', upiId);

        if (!upiId) {
            errorMessage = 'UPI ID is required.';
            document.getElementById('upi_id_error').textContent = errorMessage;
            isValid = false;
        } else {
            document.getElementById('upi_id_error').textContent = '';
        }
    }

    if (!isValid) {
        alert('Make sure you have given the valid payment Details.');
        return;
    }

    // All validations passed, show confirmation modal
    document.getElementById('confirmationModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('confirmationModal').style.display = 'none';
}

function confirmCheckout() {
    document.getElementById('checkout-form').submit();
}

function showPaymentFields() {
    const paymentMethod = document.getElementById('payment_method').value;
    document.getElementById('credit-card-fields').style.display = paymentMethod === 'Credit Card' ? 'block' : 'none';
    document.getElementById('upi-fields').style.display = paymentMethod === 'UPI' ? 'block' : 'none';
}





function toggleProfileMenu() {
        var menu = document.getElementById('profile-menu');
        menu.classList.toggle('show');
    }
        
        function toggleMenu() {
            const nav = document.querySelector('.nav');
            nav.classList.toggle('active');
        } 

    </script>
</body>
</html>
