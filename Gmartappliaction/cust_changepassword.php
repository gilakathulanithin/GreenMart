<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header('Location: login.php');
    exit();
}

include 'connectdb.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $user_id = $_SESSION['user_id'];

    // Validate the input
    if (strlen($current_password) < 6 || strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        // Fetch the current password from the database
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $error = "User not found.";
        } else {
            $row = $result->fetch_assoc();
            $stored_password = $row['password'];

            if ($current_password !== $stored_password) {
                $error = "Current password is incorrect.";
            } else {
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die('Prepare failed: ' . $conn->error);
                }
                $stmt->bind_param('si', $new_password, $user_id);
                if ($stmt->execute()) {
                    $success = "Password updated successfully.";
                } else {
                    $error = "Failed to update password.";
                }
            }
        }
    }
}
// Count total distinct products in cart
$totalCartItems = count($_SESSION['cart'] ?? []);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Change Password</title>
   
    <style>
        .change-password-form {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .change-password-form h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .change-password-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .change-password-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .change-password-form button {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .change-password-form button:hover {
            background-color: #218838;
        }

        .message {
            margin: 20px 0;
            padding: 10px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
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
    <div class="change-password-form">
       <center> <h2>Change Password</h2></center>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="user_change_password.php" onsubmit="return validateForm()">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password">

            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password">

            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password">

            <button type="submit">Change Password</button>
         
        </form>
        
    </div>
    <br><br>


    <center>
        <div class="backbutton">
                <a href="cust_dashboard.php" style="font-weight: bolder; color:green;margin:5%;">back to profile</a>
            </div>
        </center>
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
    function validateForm() {
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            alert('New passwords do not match.');
            return false;
        }
        if (currentPassword.length < 6 || newPassword.length < 6) {
            alert('Password must be at least 6 characters long.');
            return false;
        }
        return true;
    }
</script>
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