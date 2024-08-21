<?php
session_start();

include 'connectdb.php'; // Include database connection
$username=$_SESSION['username'];
// Initialize message variable
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $customer_id = intval($_POST['customer_id']); // Ensure this is set based on the logged-in user
    $complaint_text = trim($_POST['complaint_text']);
    
    // Server-side validation
    if (empty($complaint_text)) {
        $message = "Complaint text cannot be empty.";
    } elseif (strlen($complaint_text) > 1000) { // Limit length for demonstration
        $message = "Complaint text is too long. Please limit to 1000 characters.";
    } else {
        // Sanitize and prepare data
        $complaint_text = $conn->real_escape_string($complaint_text);
        $complaint_date = date('Y-m-d H:i:s');
        $status = 'pending'; // Default status for new complaints

        // Insert into complaints table
        $insert_sql = "INSERT INTO complaints (customer_id, complaint_text, complaint_date, status) 
                       VALUES ($customer_id, '$complaint_text', '$complaint_date', '$status')";

        if ($conn->query($insert_sql) === TRUE) {
            $message = "Complaint submitted successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

// Close the connection
$conn->close();

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
<h1>Submit a Complaint</h1>
    
    <?php if (!empty($message)): ?>
        <div class="message <?= (strpos($message, 'Error') !== false) ? 'error' : '' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
       <!-- <center> <label for="complaint_text" style="font-weight:bolder;">Complaint:</label></center> -->
        <textarea id="complaint_text" name="complaint_text"maxlength="100" placeholder="pleasewrite your complaint here"></textarea> <!-- Client-side validation for length -->
        <input type="hidden" name="customer_id" value="1"> <!-- Replace with dynamic user ID -->
        <button type="submit">Submit Complaint</button>
    </form>
    <br><br>
   <center>
   <div class="backbutton">
        <a href="cust_dashboard.php"style=" color:green;">back to profile</a>
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
        /* General Styles for Main Section */
main {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 800px;
  margin-top: 15%;
  margin-left:25%;
    overflow-x: auto; /* Allows horizontal scrolling on smaller screens */
}

/* Heading Style */
main h1 {
    font-size: 2em;
    margin-bottom: 20px;
    color: #333;
    text-align: center;
}

/* Message Styles */
.message {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 4px;
    font-size: 1.1em;
    text-align: center;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

label {
    font-size: 1.1em;
    color: #333;
}

textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical; /* Allows vertical resizing only */
    min-height: 120px;
    font-size: 1em;
}

button {
    background-color: green; /* Blue background for buttons */
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1.1em;
    transition: background-color 0.3s;
    align-self: center; /* Center align the button */
}

button:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

/* Responsive Design */
@media (max-width: 768px) {
    main {
        padding: 15px;
        margin-left: 0%;
        margin-top: 35%;
        margin-bottom: 20%;
    }

    textarea {
        min-height: 80px; /* Adjust min-height for smaller screens */
    }

    button {
        padding: 8px 16px;
        font-size: 1em;
    }
}

@media (max-width: 480px) {
    main {
        padding: 10px;
    }

    textarea {
        min-height: 60px; /* Further adjust min-height */
    }

    button {
        padding: 6px 12px;
        font-size: 0.9em;
    }
}

    </style>
