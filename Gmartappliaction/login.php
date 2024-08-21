<?php
session_start();
include 'connectdb.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) && empty($password)) {
        $_SESSION['error_message'] = "Username and Password are required.";
    } elseif (empty($username)) {
        $_SESSION['error_message'] = "Username is required.";
    } elseif (empty($password)) {
        $_SESSION['error_message'] = "Password is required.";
    } else {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if ($password === $user['password']) { 
                if ($user['role'] !== 'admin' && $user['status'] !== 'approved') {
                    $_SESSION['error_message'] = "Your account is pending approval. Please wait for admin approval.";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $user['firstname'] . ' ' . $user['lastname'];

                    
                    if ($user['role'] == 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: home.php");
                    }
                    exit();
                }
            } else {
                $_SESSION['error_message'] = "Invalid password.";
            }
        } else {
            $_SESSION['error_message'] = "No user found with that username.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenMart Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        input{
            font-weight: bolder;
      text-align: center;
      outline: 2px solid balck;
        }
        main {
            margin: 190px 20px; /* Adjusted for fixed header */
        }
        .login-container {

            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 500px;
            margin: 0 auto;
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        .submit{
            font-weight: bolder;
            background-color: yellowgreen;
            font-size: 25px;
         letter-spacing: 0.1rem;
            font-family: Georgia, 'Times New Roman', Times, serif;
            width: 30%;
            margin-left: 35%;
            box-shadow:  2px 2px 2px black;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
 
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
        .login-container .register-link {
            margin-top: 20px;
            display: block;
            color: green;
            text-decoration: none;
            font-weight: bold;
        }

        .login-container .register-link:hover {
            color: limegreen;
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

              
       
            <div class="menu-toggle" aria-label="Toggle Menu" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>
    <main>
        <div class="login-container">
            <h2>Login</h2>
            <form action="login.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" >

                <label for="password">Password:</label>
                <input type="password" id="password" name="password">

                <?php if (!empty($_SESSION['error_message'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
                    <?php unset($_SESSION['error_message']); // Unset the error message after displaying it ?>
                <?php endif; ?>

                <input type="submit" value="Login" class="submit">
                <a href="regerstration.php" class="register-link">Don't have an account? Register here</a>
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
</body>
</html>

