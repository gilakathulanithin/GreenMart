<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "greenmart";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all details from the website_rating table with the associated full name (first name + last name)
$sql = "
    SELECT website_rating.id, CONCAT(users.firstname, ' ', users.lastname) AS fullname, website_rating.rating, website_rating.comment 
    FROM website_rating 
    JOIN users ON website_rating.user_id = users.id
";
$result = $conn->query($sql);

// Calculate the total number of ratings, sum of ratings, and average rating
$totalRatingsSql = "SELECT COUNT(*) AS total_ratings, SUM(rating) AS total_rating_score, AVG(rating) AS average_rating FROM website_rating";
$totalRatingsResult = $conn->query($totalRatingsSql);
$totalRatings = $totalRatingsResult->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Website Ratings</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Website Ratings</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Rating</th>
            <th>Comment</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row["id"] . "</td>
                        <td>" . $row["fullname"] . "</td>
                        <td>" . $row["rating"] . "</td>
                        <td>" . $row["comment"] . "</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No results found</td></tr>";
        }
        ?>
    </tbody>
</table>

<h3>Summary</h3>
<p><strong>Total Ratings:</strong> <?php echo $totalRatings['total_ratings']; ?></p>
<p><strong>Total Rating Score:</strong> <?php echo $totalRatings['total_rating_score']; ?> out of <?php echo $totalRatings['total_ratings'] * 5; ?></p>
<p><strong>Average Rating:</strong> <?php echo number_format($totalRatings['average_rating'], 2); ?> out of 5</p>


</body>
</html>

<?php
$conn->close();
?>
