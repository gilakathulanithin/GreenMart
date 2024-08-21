<?php
session_start(); // Start the session

include 'connectdb.php'; // Include the connection script

// Initialize variables
$error_messages = [];
$success_message = '';

// Retrieve error messages from session and clear them
if (isset($_SESSION['error_messages'])) {
    $error_messages = $_SESSION['error_messages'];
    unset($_SESSION['error_messages']); // Clear error messages from session
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear success message from session
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $country = $_POST['country'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $address = trim($_POST['address']);

    $error_messages = []; // Reset error messages array

    // Validate input fields
    if (empty($firstname) || empty($lastname) || empty($username) || empty($password) || empty($email) || empty($mobile) || empty($country) || empty($state) || empty($city) || empty($address)) {
        $error_messages[] = "All fields are required.";
    }

    // Check for existing records
    if (empty($error_messages)) {
        $check_query = $conn->prepare("SELECT username, email, mobile FROM users WHERE username = ? OR email = ? OR mobile = ?");
        
        if (!$check_query) {
            $error_messages[] = "Database error: " . $conn->error;
        } else {
            $check_query->bind_param('sss', $username, $email, $mobile);
            $check_query->execute();
            $result = $check_query->get_result();
            
            // Check which fields have existing records
            while ($row = $result->fetch_assoc()) {
                if ($row['username'] == $username) {
                    $error_messages[] = "Username already exists.";
                }
                if ($row['email'] == $email) {
                    $error_messages[] = "Email already exists.";
                }
                if ($row['mobile'] == $mobile) {
                    $error_messages[] = "Mobile number already exists.";
                }
            }

            $check_query->close();
        }
    }

    // If no errors, insert the new user
    if (empty($error_messages)) {
        $query = $conn->prepare("INSERT INTO users (firstname, lastname, username, password, role, email, mobile, country, state, city, address, status) 
                                 VALUES (?, ?, ?, ?, 'user', ?, ?, ?, ?, ?, ?, 'pending')");
        
        if (!$query) {
            $error_messages[] = "Database error: " . $conn->error;
        } else {
            $query->bind_param('ssssssssss', $firstname, $lastname, $username, $password, $email, $mobile, $country, $state, $city, $address);
            
            if ($query->execute()) {
                $_SESSION['success_message'] = "You have successfully registered. Please wait for approval from the admin.";
                header('Location: regerstration.php'); // Redirect to avoid resubmission
                exit();
            } else {
                $error_messages[] = "Error: " . $query->error;
            }

            $query->close();
        }
    }

    // Store errors in session
    if (!empty($error_messages)) {
        $_SESSION['error_messages'] = $error_messages;
        header('Location: regerstration.php'); // Redirect to avoid form resubmission
        exit();
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<h
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
</head>
<body>
<style>
    main {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background-color: #f4f4f4; /* Light background for contrast */
}

.containerform {
    width: 100%;
    max-width: 600px; /* Max width for better readability on large screens */
    background-color: #ffffff; /* White background for form */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    padding: 20px;
}

.register-container {
    display: flex;
    flex-direction: column;
}

h2 {
    margin-bottom: 20px;
    color: #2e8b57; /* Dark green for headings */
    font-size: 24px;
}

label {
    margin-top: 10px;
    font-weight: bold;
    color: #333;
}

input[type="text"], select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ddd; /* Light border for input fields */
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
}

input[type="submit"] {
    background-color: #2e8b57; /* Dark green */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 20px;
    width: 100%;
}

input[type="submit"]:hover {
    background-color: #1c6f39; /* Darker green on hover */
}

pre {
    margin-top: 20px;
    font-size: 14px;
}

a.login-link {
    color: #2e8b57; /* Dark green for links */
    text-decoration: none;
}

a.login-link:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    /* Responsive adjustments */
    .container {
        padding: 10px;
    }

    input[type="submit"] {
        font-size: 14px;
    }

    h2 {
        font-size: 20px;
    }
}

</style>
        
      
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
        <div class="containerform">
            <div class="register-container">
                    <?php if (!empty($error_messages)): ?>
                        <p style="color: red;"><?php echo implode(' ', $error_messages); ?></p>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <p style="color: green;"><?php echo $success_message; ?></p>
                    <?php endif; ?>
                <h2>Register</h2>
                <form action="regerstration.php" method="post" name="myform" onsubmit="return validate()">
                    <label for="firstname">First Name:</label>
                    <input type="text" id="firstname" name="firstname" placeholder="Enter your first name">
                    
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" placeholder="Enter your last name">
                    
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username">
                    
                    <label for="password">Password:</label>
                    <input type="text" id="password" name="password" placeholder="Create a password">
                    
                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" placeholder="Enter your email">
                    
                    <label for="mobile">Mobile:</label>
                    <input type="text" id="mobile" name="mobile" placeholder="Enter your mobile number">
                    
                    <label for="country">Country:</label>
                    <select id="country" name="country">
                        <option value="">Select Country</option>
                        <!-- Add country options here -->
                    </select>
                    
                    <label for="state">State:</label>
                    <select id="state" name="state">
                        <option value="">Select State</option>
                        <!-- Add state options here -->
                    </select>
                    
                    <label for="city">City:</label>
                    <select id="city" name="city">
                        <option value="">Select City</option>
                        <!-- Add city options here -->
                    </select>
                    
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" placeholder="Enter full address">
                    
                    <input type="submit" value="Register">
    
                  <pre>Already have an account? <a href="login.php" class="login-link">Login here</a></pre>
    
      
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('country');
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');

            // Sample data
            const countries = {
                'India': ['Telangana', 'Andhra Pradesh', 'Karnataka'],
                'USA': ['California', 'Texas', 'New York'],
                'Canada': ['Ontario', 'Quebec', 'British Columbia']
            };

            const states = {
                'Telangana': ['Hyderabad', 'Nagarkurnool', 'Warangal'],
                'Andhra Pradesh': ['Kurnool', 'Vizag', 'Thirupati'],
                'Karnataka': ['Bangalore', 'Kolar', 'Tumkur'],
                'California': ['Los Angeles', 'San Francisco'],
                'Texas': ['Houston', 'Dallas'],
                'New York': ['New York City', 'Buffalo'],
                'Ontario': ['Toronto', 'Ottawa'],
                'Quebec': ['Montreal', 'Quebec City'],
                'British Columbia': ['Vancouver', 'Victoria']
            };

            // Populate country dropdown
            for (const country in countries) {
                const option = document.createElement('option');
                option.value = country;
                option.textContent = country;
                countrySelect.appendChild(option);
            }

            // Update states based on selected country
            countrySelect.addEventListener('change', function() {
                const selectedCountry = this.value;
                stateSelect.innerHTML = '<option value="">Select State</option>'; // Reset states
                citySelect.innerHTML = '<option value="">Select City</option>'; // Reset cities

                if (selectedCountry) {
                    const statesList = countries[selectedCountry];
                    statesList.forEach(state => {
                        const option = document.createElement('option');
                        option.value = state;
                        option.textContent = state;
                        stateSelect.appendChild(option);
                    });
                }
            });

            // Update cities based on selected state
            stateSelect.addEventListener('change', function() {
                const selectedState = this.value;
                citySelect.innerHTML = '<option value="">Select City</option>'; // Reset cities

                if (selectedState) {
                    const citiesList = states[selectedState];
                    citiesList.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city;
                        option.textContent = city;
                        citySelect.appendChild(option);
                    });
                }
            });
        });

        function validate() {
            var firstname = document.forms['myform']['firstname'].value.trim();
            var lastname = document.forms['myform']['lastname'].value.trim();
            var username = document.forms['myform']['username'].value.trim();
            var password = document.forms['myform']['password'].value.trim();
            var email = document.forms['myform']['email'].value.trim();
            var mobile = document.forms['myform']['mobile'].value.trim();
            var country = document.forms['myform']['country'].value;
            var state = document.forms['myform']['state'].value;
            var city = document.forms['myform']['city'].value;
            var address = document.forms['myform']['address'].value.trim();

            var usernameRegex = /^[A-Za-z][A-Za-z0-9_@$!&-]{4,19}$/;
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Simplified email regex
            var mobileRegex = /^[6-9]\d{9}$/;
            var alphabeticNameRegex = /^[A-Za-z]+$/;

            // Validate First Name
            if (firstname === "") {
                alert("Enter first name");
                return false;
            }
            if (!alphabeticNameRegex.test(firstname)) {
                alert("First name should contain only letters");
                return false;
            } 
            if (firstname.length < 3 || firstname.length > 30) {
                alert("First name should be 3 to 30 characters long");
                return false;
            }

            // Validate Last Name
            if (lastname === "") {
                alert("Enter last name");
                return false;
            }
            if (!alphabeticNameRegex.test(lastname)) {
                alert("Last name should contain only letters");
                return false;
            } 
            if (lastname.length < 3 || lastname.length > 30) {
                alert("Last name should be 3 to 30 characters long");
                return false;
            }

            // Validate Username
            if (username === "") {
                alert("Enter username");
                return false;
            }
            if (!usernameRegex.test(username)) {
                alert("Username should start with a letter and be 5 to 20 characters long, with allowed symbols _@$!&-");
                return false;
            }

            // Validate Password
            if (password === "") {
                alert("Enter password");
                return false;
            }
            if (password.length < 8 || password.length > 15) {
                alert("Password should be 8 to 15 characters long");
                return false;
            }

            // Validate Email
            if (email === "") {
                alert("Enter email");
                return false;
            }
            if (!emailRegex.test(email)) {
                alert("Enter a valid email address");
                return false;
            }

            // Validate Mobile
            if (mobile === "") {
                alert("Enter mobile number");
                return false;
            }
            if (!mobileRegex.test(mobile)) {
                alert("Enter a valid 10-digit mobile number starting with 6, 7, 8, or 9");
                return false;
            }

            // Validate Country
            if (country === "") {
                alert("Select country");
                return false;
            }

            // Validate State
            if (state === "") {
                alert("Select state");
                return false;
            }

            // Validate City
            if (city === "") {
                alert("Select city");
                return false;
            }

            // Validate Address
            if (address === "") {
                alert("Enter address");
                return false;
            }
            if (address.length < 10 || address.length > 100) {
                alert("Address should be 10 to 100 characters long");
                return false;
            }

            return true;
        }
    </script>
    
   
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
