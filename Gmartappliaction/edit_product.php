<?php
session_start();  // Ensure this is at the top

include "connectdb.php";  // Include the database connection file

if (!$conn) {
    die("Database connection not established.");
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$categories = array();
$subcategories = array();
$existing_images = array();

// Fetch categories
$sql = "SELECT id, name FROM categories";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch subcategories
$sql = "SELECT id, name, category_id FROM subcategories";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
}

// Fetch product details
if ($product_id) {
    $sql = "SELECT p.id, p.name, p.description, p.price, p.category_id, p.subcategory_id, c.name AS category, s.name AS subcategory
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN subcategories s ON p.subcategory_id = s.id
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        die("Product not found.");
    }

    // Fetch existing images
    $sql_images = "SELECT image_path FROM product_images WHERE product_id = ?";
    $stmt_images = $conn->prepare($sql_images);
    $stmt_images->bind_param("i", $product_id);
    $stmt_images->execute();
    $result_images = $stmt_images->get_result();
    while ($row = $result_images->fetch_assoc()) {
        $existing_images[] = $row['image_path'];
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $subcategory_id = $_POST['subcategory_id'];

    $update_sql = "UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, subcategory_id = ? WHERE id = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("ssdiii", $name, $description, $price, $category_id, $subcategory_id, $product_id);

    if ($stmt_update->execute()) {
        // Check if new images were uploaded
        if (!empty($_FILES['product_images']['name'][0])) {
            // Remove old images
            foreach ($existing_images as $image_path) {
                unlink($image_path);
            }

            $sql_delete_images = "DELETE FROM product_images WHERE product_id = ?";
            $stmt_delete_images = $conn->prepare($sql_delete_images);
            $stmt_delete_images->bind_param("i", $product_id);
            $stmt_delete_images->execute();

            // Handle new file upload
            $upload_dir = __DIR__ . '/uploads/';
            $uploaded_images = array();

            foreach ($_FILES['product_images']['name'] as $key => $value) {
                if ($_FILES['product_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['product_images']['tmp_name'][$key];
                    $file_name = basename($value);
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($file_tmp, $file_path)) {
                        $uploaded_images[] = $file_path;
                    }
                }
            }

            foreach ($uploaded_images as $file_path) {
                $sql_image = "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)";
                $stmt_image = $conn->prepare($sql_image);
                $stmt_image->bind_param("is", $product_id, $file_path);
                $stmt_image->execute();
            }
        }

        header('Location: get_products.php');
        exit;
    } else {
        echo "Error updating product: " . $conn->error;
    }
}
// Fetch the count of pending approvals
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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="./drop.css">
    <title>GreenMart Dashboard</title>
    <style>
        /* General container styling */
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        /* Form styling */
        .product-form {
            display: flex;
            flex-direction: column;
        }

        /* Label and input styling */
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"], textarea, select {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }

        /* File input styling */
        input[type="file"] {
            margin-bottom: 15px;
        }

        /* Button styling */
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Image preview styling */
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-preview img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        main {
            margin-left: 220px;
            margin-top: 50px;
            padding: 2rem;
            flex: 1;
        }



        @media (max-width: 768px) {
            #menu-toggle {
                display: block;
            }

            #menu-toggle-label {
                display: block;
                margin-left: 1rem;
            }


            main {
                margin-left: 0;
                margin-top: 50px;
                padding: 1rem;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-content">
            <label for="menu-toggle" id="menu-toggle-label"><i class="fas fa-bars"></i></label>
            <input type="checkbox" id="menu-toggle">
            <h1><span>GreenMart</span> </h1>
        </div>
    </header>
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
                        <!-- <a href="pending_orders.php"><i class="fas fa-clock"></i> Pending Products</a> -->
                        <a href="delivered_orders.php"><i class="fas fa-box"></i> Delivered Orders</a>
                        <a href="shipped_orders.php"><i class="fas fa-shipping-fast"></i> Shipped Orders</a>
                        <a href="cancelled_orders.php"><i class="fas fa-times-circle"></i> Cancelled Orders</a>
                    </div>
        </li>
            <li><a href=""><i class="fas fa-info-circle"></i> Inquiries</a></li>
            <li><a href=""><i class="fas fa-percentage"></i> Discounts</a></li>
            <li><a href=""><i class="fas fa-warehouse"></i> Inventory Reports</a></li>
            <li><a href=""><i class="fas fa-key"></i> Change Password</a></li>
            <li><a href="logout.php?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

            </ul>
        </nav>
    </aside>
    <main>
    <div class="container">
        <h1>Edit Product</h1>
        <form method="POST" action="edit_product.php?id=<?php echo htmlspecialchars($product_id); ?>" enctype="multipart/form-data">
            <div class="product-form">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required onchange="filterSubcategories(this.value)">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="subcategory_id">Subcategory:</label>
                <select id="subcategory_id" name="subcategory_id">
                    <option value="">Select Subcategory</option>
                </select>
                <input type="hidden" id="subcategoriesData" value='<?php echo json_encode($subcategories); ?>'>
                <input type="hidden" id="selected_subcategory" value="<?php echo htmlspecialchars($product['subcategory_id']); ?>">

                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>

                <label for="product_images">Upload Images:</label>
                <input type="file" id="product_images" name="product_images[]" multiple accept="image/*" onchange="previewImages(this)">

                <div class="image-preview"></div>

                <button type="submit">Update Product</button>
            </div>
        </form>
    </div>
    </main>
    <footer>
        <p>&copy; 2024 GreenMart</p>
    </footer>
    <script>
    
        function filterSubcategories(categoryId) {
            var subcategorySelect = document.getElementById('subcategory_id');
            var subcategories = JSON.parse(document.getElementById('subcategoriesData').value);

            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            subcategories.forEach(function(subcategory) {
                if (subcategory.category_id == categoryId) {
                    var option = document.createElement('option');
                    option.value = subcategory.id;
                    option.text = subcategory.name;
                    subcategorySelect.appendChild(option);
                }
            });

            // Set the selected subcategory
            var selectedSubcategory = document.getElementById('selected_subcategory').value;
            if (selectedSubcategory) {
                subcategorySelect.value = selectedSubcategory;
            }
        }

        function previewImages(inputElement) {
            var preview = document.querySelector('.image-preview');
            var files = inputElement.files;

            preview.innerHTML = '';
            if (files) {
                Array.from(files).forEach(file => {
                    var img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    preview.appendChild(img);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Populate subcategories based on the selected category on page load
            var categorySelect = document.getElementById('category_id');
            var selectedCategory = categorySelect.value;
            var selectedSubcategory = document.getElementById('selected_subcategory').value;

            filterSubcategories(selectedCategory);

            // Populate existing images
            var preview = document.querySelector('.image-preview');
            <?php foreach ($existing_images as $image): ?>
                var img = document.createElement('img');
                img.src = 'uploads/' + <?= json_encode(basename($image)); ?>;
                preview.appendChild(img);
            <?php endforeach; ?>
        });

        document.addEventListener('DOMContentLoaded', () => {
            const menuToggle = document.querySelector('#menu-toggle');
            const sidebar = document.querySelector('#sidebar');

            menuToggle.addEventListener('change', () => {
                if (menuToggle.checked) {
                    sidebar.classList.add('show');
                } else {
                    sidebar.classList.remove('show');
                }
            });

            document.querySelector('#sidebar-logout').addEventListener('click', () => {
                // Handle logout functionality
                alert('Logged out successfully!');
            });
        });
    </script>
</body>
</html>
