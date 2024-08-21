<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

include "connectdb.php";
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Validate month and year
if ($month < 1 || $month > 12) {
    $month = date('m');
}
if ($year < 2000 || $year > date('Y')) {
    $year = date('Y');
}

// Define the start and end dates of the selected month
$start_date = "$year-$month-01";
$end_date = date('Y-m-t', strtotime($start_date));

// Fetch Products
$product_sql = "SELECT p.id, p.name, c.name AS category, p.quantity, p.price
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id";
$product_result = $conn->query($product_sql);

if (!$product_result) {
    die("Error executing product query: " . $conn->error . " in query: " . $product_sql);
}

$products = array(); // Initialized as array()
$product_count = 0;

if ($product_result->num_rows > 0) {
    $products = $product_result->fetch_all(MYSQLI_ASSOC);
    $product_count = $product_result->num_rows;
}

// Fetch Categories
$category_sql = "SELECT * FROM categories";
$category_result = $conn->query($category_sql);

if (!$category_result) {
    die("Error executing category query: " . $conn->error . " in query: " . $category_sql);
}

$categories = array(); // Initialized as array()
if ($category_result->num_rows > 0) {
    $categories = $category_result->fetch_all(MYSQLI_ASSOC);
}

// Fetch Sales for the selected month
$sale_sql = "SELECT p.name AS product, s.quantity_sold, s.sale_price, s.sale_date
             FROM sales s
             LEFT JOIN products p ON s.product_id = p.id
             WHERE s.sale_date BETWEEN '$start_date' AND '$end_date'";
$sale_result = $conn->query($sale_sql);

if (!$sale_result) {
    die("Error executing sales query: " . $conn->error . " in query: " . $sale_sql);
}

$sales = array(); // Initialized as array()
$sale_count = 0;

if ($sale_result->num_rows > 0) {
    $sales = $sale_result->fetch_all(MYSQLI_ASSOC);
    $sale_count = $sale_result->num_rows;
}

// Fetch Orders for the selected month
$order_sql = "SELECT o.id, o.total_amount, o.order_date, o.status
              FROM orders o
              WHERE o.order_date BETWEEN '$start_date' AND '$end_date'";
$order_result = $conn->query($order_sql);

if (!$order_result) {
    die("Error executing orders query: " . $conn->error . " in query: " . $order_sql);
}

$orders = array(); // Initialized as array()
$order_count = 0;

if ($order_result->num_rows > 0) {
    $orders = $order_result->fetch_all(MYSQLI_ASSOC);
    $order_count = $order_result->num_rows;
}// Fetch the count of pending approvals
$sql = "SELECT COUNT(*) AS pending_count FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pending_count = $row['pending_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./drop.css">
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        



        .content {
            flex: 1;
            padding: 20px;
            margin-left: 250px; /* Adjusts content to accommodate the sidebar */
            background: #ecf0f1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    
        .main-content {
            margin-left: 10%;
            margin-right: 10%;
        margin-top: 5%;
            width: 80%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap:2%;
        }
    
     
     
        @media (max-width: 768px) {
       
            .main-content {
            width: 100%;
            max-width: 400px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .content {
            margin-left: 0; /* Adjust content margin for small screens */
        }
    }
    @media (max-width: 576px) {
        
    }
/* General Styles */
body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f7fa;
    color: #333;
}

main.content {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    color: #2c3e50;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 25px;
}

/* Form Styles */
form {
    display: flex;
    justify-content:center;
    align-items: center;
    background-color: #ecf0f1;
    padding: 0px;
    border-radius: 10px;
    margin-bottom: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

form label {
    font-weight: 600;
    color: #2c3e50;
    margin-right: 15px;
}

form select, 
form input {
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #bdc3c7;
    margin-right: 10px;
    flex: 1 1 200px;
    font-size: 16px;
    color: #34495e;
}

form button {
    padding: 12px 25px;
    background-color: green;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    font-size: 16px;
    font-weight: 600;
}



/* Navigation Buttons */
div > button {
    padding: 12px 20px;
    border: 2px solid green;
    background-color: white;
    color: green;
    cursor: pointer;
    border-radius: 5px;
    transition: all 0.3s;
    margin-right: 10px;
    font-size: 16px;
    font-weight: 600;
}

div > button:hover {
    background-color: green;
    color: white;
}

div > button.active {
    background-color: #3498db;
    color: white;
}

/* Sections */
.section {
    display: none;
    padding: 25px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.section.active {
    display: block;
}

.section h2 {
    color: #2c3e50;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 20px;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}

table th, table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ecf0f1;
    font-size: 16px;
    color: #34495e;
}

table th {
    background-color: green;
    color: white;
    font-weight: 600;
}

table tr:hover {
    background-color: #ecf0f1;
}

.no-data {
    color: #7f8c8d;
    font-style: italic;
    text-align: center;
    margin-top: 15px;
    font-size: 16px;
}

/* Responsive Design */
@media (max-width: 768px) {
    form {
        flex-direction: column;
        gap: 15px;
    }

    form select, 
    form input {
        margin-right: 0;
        margin-bottom: 10px;
        flex: 1 1 100%;
    }

    form button {
        align-self: center;
        width: 100%;
    }

    div > button {
        margin-bottom: 10px;
        width: 100%;
    }
}



    </style>
</head>
<body>
<header>
        <div class="header-content">
            <label for="menu-toggle" id="menu-toggle-label"><i class="fas fa-bars"></i></label>
            <input type="checkbox" id="menu-toggle" hidden>
            <h1><span>GreenMart</span> </h1>
        </div>
    </header>
    
    <button id="menu-toggle" class="menu-toggle">☰</button>

    <div class="dashboard">
    <aside id="sidebar">
        <br><br><br><br>
        <div class="profile">
            <!-- <img src="https://via.placeholder.com/40" alt="Profile Picture"> -->
          
            <!-- <img src="https://via.placeholder.com/40" alt="Profile Picture"> -->
            <a href="admin_dashboard.php" style="text-decoration:none;color:blue; "><span><i class="fas fa-user"></i><?php echo htmlspecialchars($_SESSION['username']); ?> </span></a>
 
        </div>
        <nav>
            <ul>
          
            <!-- <a href="user_approvals.php">User Approvals (<?php echo $pending_count; ?>)</a> -->
                <!-- <li><a href="dsh.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li> -->
             <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Add Products</a></li>
            <li><a href="get_products.php"><i class="fas fa-box-open"></i> View Products</a></li>
            <li><a href="view_customer.php"><i class="fas fa-user-friends"></i> View Customers</a></li>
            <li><a href="user_approvals.php"><i class="fas fa-user-check"></i> User Approvals (<?php echo $pending_count; ?>)</a></li>
                   <!-- Order Management Dropdown -->
        <li class="dropdown">
            <a href="order_management.php" class="dropbtn"><i class="fas fa-clipboard-list"></i> Order Management</a>
            <div class="dropdown-content">
                        <a href="pending_orders.php"><i class="fas fa-clock"></i> Pending Products</a>
                        <a href="delivered_orders.php"><i class="fas fa-box"></i> Delivered Orders</a>
                        <a href="shipped_orders.php"><i class="fas fa-shipping-fast"></i> Shipped Orders</a>
                        <a href="cancelled_orders.php"><i class="fas fa-times-circle"></i> Cancelled Orders</a>
                    </div>
        </li>
            <!-- <li><a href=""><i class="fas fa-info-circle"></i> Inquiries</a></li> -->
            <li><a href=""><i class="fas fa-percentage"></i> Discounts</a></li>
            <li><a href="inventory_report.php"><i class="fas fa-warehouse"></i> Inventory Reports</a></li>
            <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
            <li><a href="logout.php?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

            </ul>
        </nav>
    </aside>
        <main class="content">
        <h1>GreenMart Inventory Report</h1>

<!-- Date Picker -->
<div>
    <form method="GET" action="">
        <label for="month">Month:</label>
        <select id="month" name="month">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                </option>
            <?php endfor; ?>
        </select>
        <br>
        <label for="year">Year:</label>
        <input type="number" id="year" name="year" value="<?= $year ?>" min="2000" max="<?= date('Y') ?>">
        <button type="submit">Filter</button>
    </form>
</div>

<!-- Navigation Buttons -->
<div>
    <button onclick="showSection('products')">Products</button>
    <button onclick="showSection('categories')">Categories</button>
    <button onclick="showSection('sales')">Sales</button>
    <button onclick="showSection('orders')">Orders</button>
</div>

<!-- Products Section -->
<div id="products" class="section active">
    <h2>Products</h2>
    <?php if ($product_count > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td><?= $product['name'] ?></td>
                        <td><?= $product['category'] ?></td>
                        <td><?= $product['quantity'] ?></td>
                        <td><?= $product['price'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No products found for the selected month.</p>
    <?php endif; ?>
</div>

<!-- Categories Section -->
<div id="categories" class="section">
    <h2>Categories</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= $category['id'] ?></td>
                    <td><?= $category['name'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Sales Section -->
<div id="sales" class="section">
    <h2>Sales</h2>
    <?php if ($sale_count > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity Sold</th>
                    <th>Sale Price</th>
                    <th>Sale Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= $sale['product'] ?></td>
                        <td><?= $sale['quantity_sold'] ?></td>
                        <td><?= $sale['sale_price'] ?></td>
                        <td><?= $sale['sale_date'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No sales data for the selected month.</p>
    <?php endif; ?>
</div>

<!-- Orders Section -->
<div id="orders" class="section">
    <h2>Orders</h2>
    <?php if ($order_count > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= $order['total_amount'] ?></td>
                        <td><?= $order['order_date'] ?></td>
                        <td><?= $order['status'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No orders data for the selected month.</p>
    <?php endif; ?>
</div>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2024 GreenMart. All Rights Reserved.</p>
    </footer>

    <script>
        
        document.querySelector('.dropdown').addEventListener('click', function(event) {
    event.stopPropagation();
    this.querySelector('.dropdown-content').classList.toggle('show');
});

window.onclick = function(event) {
    if (!event.target.matches('.dropbtn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
};

        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.querySelector('.sidebar');
        
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        });
        function showSection(sectionId) {
            var sections = document.getElementsByClassName('section');
            for (var i = 0; i < sections.length; i++) {
                sections[i].classList.remove('active');
            }
            document.getElementById(sectionId).classList.add('active');
        }
    </script>
</body>
</html>
