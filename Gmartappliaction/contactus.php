<?php
session_start();

include 'connectdb.php'; // Include database connection

// Check if logout request is made
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_unset();
    session_destroy();
    header("Location: template.php"); // Adjust the redirection as needed
    exit();
}

// Initialize form data and errors
$fullName = $email = $phone = $subject = $message = '';
$errors = [];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form input
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate form input
    if (empty($fullName)) {
        $errors['fullName'] = 'Full Name is required.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (strpos($email, '@gmail.com') === false) {
        $errors['email'] = 'Please use a Gmail address.';
    }

    if (!empty($phone) && !preg_match('/^[+]*[0-9]{1,4}[ -]?([0-9]{1,4}[ -]?)*[0-9]+$/', $phone)) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }

    if (empty($subject)) {
        $errors['subject'] = 'Subject is required.';
    }

    if (empty($message)) {
        $errors['message'] = 'Message is required.';
    }

    // If there are validation errors, redirect back with errors
    if (empty($errors)) {
        // Prepare and execute SQL statement
        $stmt = $conn->prepare("INSERT INTO contact_messages (full_name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die('Prepare Error: ' . $conn->error);
        }

        $stmt->bind_param('sssss', $fullName, $email, $phone, $subject, $message);

        if ($stmt->execute()) {
            // Unset session variables for errors and form data
            unset($_SESSION['errors']);
            unset($_SESSION['form_data']);
            
            // Redirect to a success page or show a success message
            header('Location: thankyou.php'); // Redirect to a thank-you page
            exit;
        } else {
            echo 'Execute Error: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST; // Save form data to repopulate fields
        header('Location: contactus.php'); // Redirect to the contact form page
        exit;
    }
}

$conn->close();
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
    <section class="contact-form">
       <center> <h2>Send Us  Message</h2></center>
        <form action="contactus.php" method="POST" onsubmit="return validateForm()">
            <label for="fullName">Full Name:</label>
            <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($_SESSION['form_data']['fullName'] ?? '') ?>">
            <span class="error"><?= htmlspecialchars($_SESSION['errors']['fullName'] ?? '') ?></span>

            <label for="email">Email Address:</label>
            <input type="text" id="email" name="email" value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? '') ?>">
            <span class="error"><?= htmlspecialchars($_SESSION['errors']['email'] ?? '') ?></span>

            <label for="phone">Phone Number (optional):</label>
            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($_SESSION['form_data']['phone'] ?? '') ?>">
            <span class="error"><?= htmlspecialchars($_SESSION['errors']['phone'] ?? '') ?></span>

            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($_SESSION['form_data']['subject'] ?? '') ?>">
            <span class="error"><?= htmlspecialchars($_SESSION['errors']['subject'] ?? '') ?></span>

            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="5"><?= htmlspecialchars($_SESSION['form_data']['message'] ?? '') ?></textarea>
            <span class="error"><?= htmlspecialchars($_SESSION['errors']['message'] ?? '') ?></span>

            <!-- <ul id="error-list" style="color: red; display: <?= !empty($_SESSION['errors']) ? 'block' : 'none' ?>;">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul> -->

            <button type="submit">Send</button>
        </form>
    </section>



        <section class="contact-info">
            <h2>Contact Information</h2>
            <p><strong>Customer Service Phone:</strong> 7893958616</p>
            <p><strong>Email Support:</strong> support@greenmart.com</p>
            <p><strong>Store Address:</strong>1-69,banaganapalli city TG 518865</p>
            <p><strong>Business Hours:</strong> Mon - Fri: 9 AM - 6 PM, Sat: 10 AM - 4 PM, Sun: Closed</p>
        </section>

        <section class="map">
            <h2>Find Us</h2>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3787.022640296394!2d79.44420937470393!3d18.346251524476703!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a332d9568408051%3A0x3ca346e5ac2766fe!2z4LCV4LCq4LGB4LCy4LCq4LCy4LGN4LCy4LC_LCDgsKTgsYbgsLLgsILgsJfgsL7gsKM!5e0!3m2!1ste!2sin!4v1724131091670!5m2!1ste!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
function validateForm() {
            let isValid = true;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;

            // Clear previous errors
            document.querySelectorAll('.error').forEach(el => el.textContent = '');

            // Validate email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                document.querySelector('#email + .error').textContent = 'Please enter a valid email address.';
                isValid = false;
            } else if (!email.endsWith('@gmail.com')) {
                document.querySelector('#email + .error').textContent = 'Please use a Gmail address.';
                isValid = false;
            }

            // Validate phone
          // Validate phone
          const phonePattern = /^[6789][0-9]{9}$/;
            if (phone && !phonePattern.test(phone)) {
                document.querySelector('#phone + .error').textContent = 'Please enter a valid phone number (10 digits, starting with 6, 7, 8, or 9).';
                isValid = false;
            }

            return isValid;
        }
    </script>
<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
main {
    padding: 2em;
    margin-top: 10%; /* Space for fixed header */
    background-color: #f4f4f4;
    font-family: 'Open Sans', sans-serif;
}

/* Contact Intro */
.contact-intro {
    text-align: center;
    margin-bottom: 3em;
}

.contact-intro h1 {
    font-size: 2.8em;
    color: #333;
    margin-bottom: 0.5em;
    font-family: 'Roboto', sans-serif;
    font-weight: 700;
    line-height: 1.2;
}

.contact-intro p {
    font-size: 1.2em;
    color: #666;
    font-family: 'Open Sans', sans-serif;
    line-height: 1.6;
}

/* Contact Form Styles */
.contact-form {
    max-width: 700px;
    margin: 0 auto;
    background: #fff;
    padding: 2em;
    border-radius: 10px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.contact-form h2 {
    font-size: 2em;
    color: #4CAF50;
    margin-bottom: 1.5em;
    font-family: 'Roboto', sans-serif;
    font-weight: 700;
}

.contact-form label {
    display: block;
    margin-top: 1em;
    font-weight: 600;
    color: #333;
}

.contact-form input, .contact-form textarea {
    width: 100%;
    padding: 0.8em;
    margin-top: 0.5em;
    border-radius: 6px;
    border: 1px solid #ccc;
    box-sizing: border-box;
    font-size: 1em;
    font-family: 'Open Sans', sans-serif;
}

.contact-form textarea {
    resize: vertical;
    min-height: 150px;
}

.contact-form button {
    background: #4CAF50;
    color: #fff;
    border: none;
    padding: 0.8em 1.6em;
    cursor: pointer;
    margin-top: 1.5em;
    border-radius: 6px;
    font-size: 1.1em;
    font-family: 'Open Sans', sans-serif;
    transition: background 0.3s ease;
}

.contact-form button:hover {
    background: #45a049;
}

/* Error List */
#error-list {
    margin: 1em 0;
    padding: 0;
    list-style: none;
    color: #f44336;
    font-size: 0.9em;
}

/* Contact Information Styles */
.contact-info {
    max-width: 700px;
    margin: 2em auto;
    background: #fff;
    padding: 2em;
    border-radius: 10px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.contact-info h2 {
    font-size: 2em;
    color: #4CAF50;
    margin-bottom: 1.5em;
    font-family: 'Roboto', sans-serif;
    font-weight: 700;
}

.contact-info p {
    font-size: 1.1em;
    margin: 0.5em 0;
    color: #666;
    line-height: 1.6;
}

/* Map Styles */
.map {
    max-width: 1000px;
    margin: 2em auto;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.map iframe {
    width: 100%;
    height: 400px;
    border: 0;
    border-radius: 10px;
}

/* Social Media Styles */
.social-media {
    text-align: center;
    margin: 2em 0;
}

.social-media h2 {
    font-size: 2em;
    color: #4CAF50;
    margin-bottom: 1em;
    font-family: 'Roboto', sans-serif;
    font-weight: 700;
}

.social-media a {
    display: inline-block;
    margin: 0 1em;
    color: #4CAF50;
    text-decoration: none;
    font-size: 1.2em;
    transition: color 0.3s ease;
}

.social-media a:hover {
    color: #45a049;
}

/* Responsive Design for Main Content */
@media (max-width: 1024px) {
    .contact-form, .contact-info {
        padding: 1.5em;
        margin: 1em;
    }

    .contact-form h2, .contact-info h2 {
        font-size: 1.8em;
    }

    .contact-intro h1 {
        font-size: 2.4em;
    }

    .contact-intro p {
        font-size: 1em;
    }

    .map iframe {
        height: 300px;
    }
}

@media (max-width: 768px) {
    .contact-form, .contact-info {
        padding: 1em;
        margin: 1em;
    }

    .contact-form h2, .contact-info h2 {
        font-size: 1.6em;
    }

    .contact-intro h1 {
        font-size: 2em;
    }

    .contact-intro p {
        font-size: 0.9em;
    }

    .map iframe {
        height: 250px;
    }

    .social-media a {
        font-size: 1.1em;
        margin: 0 0.5em;
    }
}

@media (max-width: 480px) {
    .contact-intro h1 {
        font-size: 1.6em;
    }

    .contact-intro p {
        font-size: 0.8em;
    }

    .contact-form h2, .contact-info h2 {
        font-size: 1.4em;
    }

    .map iframe {
        height: 200px;
    }

    .social-media a {
        display: block;
        margin: 0.5em 0;
    }
}

.contact-form { width: 90%; max-width: 600px; margin: auto; }
        .contact-form label { display: block; margin-top: 10px; }
        .contact-form input, .contact-form textarea { width: 100%; padding: 8px; margin: 5px 0 10px; border: 1px solid #ccc; border-radius: 4px; }
        .contact-form .error { color: red; font-size: 0.875em; }
        .contact-form button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .contact-form button:hover { background-color: #45a049; }
</style>