<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

include 'connectdb.php';

if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: login.php");
    exit();
}

$categories = [];
$subcategories = [];

// Fetch categories
$sql = "SELECT id, name FROM categories";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch subcategories
$sql = "SELECT id, name, category_id FROM subcategories";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $subcategories = $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST['category'];
    $subcategory_id = $_POST['subcategory'];
    $product_name = $_POST['productName'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $measurement_unit = $_POST['measurement_unit'];

    // Insert product details
    $sql = "INSERT INTO products (category_id, subcategory_id, name, description, price, quantity, measurement_unit) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iissdis', $category_id, $subcategory_id, $product_name, $description, $price, $quantity, $measurement_unit);

    if ($stmt->execute()) {
        $product_id = $stmt->insert_id;
        $upload_dir = __DIR__ . '/uploads/';

        // Handle multiple file uploads
        if (!empty($_FILES['productImages']['name'][0])) {
            foreach ($_FILES['productImages']['name'] as $key => $value) {
                $file_tmp = $_FILES['productImages']['tmp_name'][$key];
                $file_name = basename($_FILES['productImages']['name'][$key]);
                $file_path = $upload_dir . $file_name;

                if ($_FILES['productImages']['error'][$key] === UPLOAD_ERR_OK) {
                    if (move_uploaded_file($file_tmp, $file_path)) {
                        $sql = "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('is', $product_id, $file_path);
                        if (!$stmt->execute()) {
                            echo "Error inserting image path into database: " . $stmt->error . "<br>";
                        }
                    } else {
                        echo "Error moving file: $file_name<br>";
                    }
                } else {
                    echo "Error uploading file: " . $_FILES['productImages']['error'][$key] . "<br>";
                }
            }
        }

        echo "New product added successfully";
        header('location:get_products.php');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$sql = "SELECT COUNT(*) AS pending_count FROM users WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$pending_count = $row['pending_count'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenMart Dashboard</title>
    <link rel="stylesheet" href="./drop.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        main {
            margin-top: 50px;
            max-width: 1000px;
            margin-right: 70px;
            margin-left: auto;
            width: 100%;
        }
        /* .adminhead {
            text-align: center;
            margin-bottom: 0rem;
        }
        .adminhead h1 {
            font-size: 2rem;
            color: #4CAF50;
        } */
        .container {
            height: 110vh;
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 10%;
        }
        .buttoncontainer {
            display: flex;
            justify-content: space-around;
            margin-bottom: 10%;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        .product-form {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .product-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .product-form input,
        .product-form select,
        .product-form textarea {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        .product-form textarea {
            height: 100px;
            resize: vertical;
        }
        .image-preview img {
            max-width: 100px;
            max-height: 100px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .add-product-button {
            width: 30%;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            margin-top: 1rem;
        }
        .add-product-button:hover {
            background-color: #45a049;
        }
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
            .product-form {
                padding: 1rem;
            }
            main {
                margin-right: 0;
                margin-left: 0;
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
            <h1><span>GreenMart</span></h1>
        </div>
    </header>
    
    <aside id="sidebar">
        <br><br><br><br>
        <div class="profile">
            <a href="admin_dashboard.php" style="text-decoration:none;color:blue;"><span><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span></a>
        </div>
        <nav>
            <ul>
                <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Add Products</a></li>
                <li><a href="get_products.php"><i class="fas fa-box-open"></i> View Products</a></li>
                <li><a href="view_customer.php"><i class="fas fa-user-friends"></i> View Customers</a></li>
                <li><a href="user_approvals.php"><i class="fas fa-user-check"></i> User Approvals (<?php echo $pending_count; ?>)</a></li>
                <li class="dropdown">
                    <a href="order_management.php" class="dropbtn"><i class="fas fa-clipboard-list"></i> Order Management</a>
                    <div class="dropdown-content">
                        <a href="pending_orders.php"><i class="fas fa-clock"></i> Pending Products</a>
                        <a href="delivered_orders.php"><i class="fas fa-box"></i> Delivered Orders</a>
                        <a href="shipped_orders.php"><i class="fas fa-shipping-fast"></i> Shipped Orders</a>
                        <a href="cancelled_orders.php"><i class="fas fa-times-circle"></i> Cancelled Orders</a>
                    </div>
                </li>
                <!-- <li><a href="#"><i class="fas fa-info-circle"></i> Inquiries</a></li> -->
                <li><a href="#"><i class="fas fa-percentage"></i> Discounts</a></li>
                <li><a href="inventory_report.php"><i class="fas fa-warehouse"></i> Inventory Reports</a></li>
                <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                <li><a href="logout.php?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main>
        <div class="adminhead">
            <h1>Add New Product</h1>
        </div>
        <div class="container">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="product-form">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="" disabled selected>Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="subcategory">Subcategory</label>
                    <select id="subcategory" name="subcategory" required>
                        <option value="" disabled selected>Select Subcategory</option>
                        <?php foreach ($subcategories as $subcategory): ?>
                            <option value="<?php echo $subcategory['id']; ?>" data-category="<?php echo $subcategory['category_id']; ?>">
                                <?php echo htmlspecialchars($subcategory['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="productName">Product Name</label>
                    <input type="text" id="productName" name="productName" required>

                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>

                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>

                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" required>

                    <label for="measurement_unit">Measurement Unit</label>
                    <input type="text" id="measurement_unit" name="measurement_unit" required>

                    <label for="productImages">Product Images (select multiple)</label>
                    <input type="file" id="productImages" name="productImages[]" multiple accept="image/*">
                    
                    <button type="submit" class="add-product-button">Add Product</button>
                </div>
            </form>
        </div>
    </main>
    <footer class="footer">
        <p>&copy; 2024 GreenMart. All Rights Reserved.</p>
    </footer>

    <script>
        document.getElementById('category').addEventListener('change', function() {
            const selectedCategory = this.value;
            const subcategorySelect = document.getElementById('subcategory');
            subcategorySelect.querySelectorAll('option').forEach(option => {
                if (option.dataset.category == selectedCategory) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
            subcategorySelect.value = '';
        });
        document.getElementById('subcategory').addEventListener('change', function() {
    const subcategoryId = this.value;
    const unitSelect = document.getElementById('unit');

    // Clear existing options
    unitSelect.innerHTML = '<option value="" disabled selected>Select Unit</option>';

    if (subcategoryId) {
        fetch(`get_units.php?subcategory_id=${subcategoryId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.unit;
                    unitSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching units:', error));
    }
});
    </script>
</body>
</html>
