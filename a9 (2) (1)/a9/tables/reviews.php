<?php
/**
 * CPS510 A9 - Reviews CRUD Page
 */
require_once '../config.php';

$error_message = '';
$success_message = '';

// ------------------
// ADD REVIEW LOGIC
// ------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $sql = "INSERT INTO Review (ReviewID, UserID, ProductID, Rating, ReviewComment, ReviewDate) 
                VALUES (:rid, :ruid, :rpid, :rrating, :rcomment, TO_DATE(:rdate, 'YYYY-MM-DD'))";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':rid', $_POST['review_id']);
        oci_bind_by_name($stmt, ':ruid', $_POST['user_id']);
        oci_bind_by_name($stmt, ':rpid', $_POST['product_id']);
        oci_bind_by_name($stmt, ':rrating', $_POST['rating']);
        oci_bind_by_name($stmt, ':rcomment', $_POST['comment']);
        oci_bind_by_name($stmt, ':rdate', $_POST['review_date']);
        
        if (@oci_execute($stmt)) {
            oci_commit($conn);
            $success_message = "✓ Review added successfully!";
        } else {
            $error = oci_error($stmt);
            if ($error['code'] == 1) {
                $error_message = "Error: Review ID {$_POST['review_id']} already exists!";
            } elseif ($error['code'] == 2291) {
                $error_message = "Error: Invalid User ID or Product ID!";
            } elseif ($error['code'] == 2290) {
                $error_message = "Error: Rating must be between 1 and 5!";
            } else {
                $error_message = "Error: " . $error['message'];
            }
        }
    }

    oci_close($conn);
    if (empty($error_message) && empty($success_message)) {
        header('Location: reviews.php');
        exit;
    }
}

// ----------------------
// SEARCH FUNCTION LOGIC
// ----------------------
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$whereClause = "";
if ($search !== "") {
    $whereClause = "WHERE 
        LOWER(r.ReviewID) LIKE LOWER(:search)
        OR LOWER(u.FirstName) LIKE LOWER(:search)
        OR LOWER(u.LastName) LIKE LOWER(:search)
        OR LOWER(p.Name) LIKE LOWER(:search)
        OR LOWER(r.ReviewComment) LIKE LOWER(:search)
        OR r.Rating = :search_exact";
}

// ----------------------
// LOAD REVIEW DATA
// ----------------------
$conn = getDBConnection();

$sql = "SELECT r.*, u.FirstName, u.LastName, p.Name AS ProductName 
        FROM Review r 
        JOIN Users u ON r.UserID = u.UserID 
        JOIN Product p ON r.ProductID = p.ProductID 
        $whereClause
        ORDER BY r.ReviewID DESC";

$stmt = oci_parse($conn, $sql);

if ($search !== "") {
    $searchWildcard = "%$search%";
    oci_bind_by_name($stmt, ':search', $searchWildcard);
    // Bind exact rating for precise rating search
    $search_exact = $search;
    oci_bind_by_name($stmt, ':search_exact', $search_exact);
}

oci_execute($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Reviews</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        h1 { color: #333; margin-bottom: 30px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .actions a {
            margin: 0 5px;
            color: #667eea;
            text-decoration: none;
        }
        .stars { color: #f59e0b; }

        .error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #166534;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Search Bar Styling */
        .search-bar {
            margin: 25px 0;
            display: flex;
            gap: 10px;
        }
        .search-bar input {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⭐ Manage Reviews</h1>
        
        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <h2>Add New Review</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label>Review ID:</label>
                <input type="number" name="review_id" required>
            </div>
            <div class="form-group">
                <label>User ID:</label>
                <input type="number" name="user_id" required>
            </div>
            <div class="form-group">
                <label>Product ID:</label>
                <input type="number" name="product_id" required>
            </div>
            <div class="form-group">
                <label>Rating (1-5):</label>
                <select name="rating" required>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>
            </div>
            <div class="form-group">
                <label>Review Comment:</label>
                <textarea name="comment" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label>Review Date:</label>
                <input type="date" name="review_date" required>
            </div>

            <button type="submit">Add Review</button>
        </form>

        <!-- SEARCH BAR -->
        <h2 style="margin-top: 40px;">Search Reviews</h2>
        <form method="GET" class="search-bar">
            <input 
                type="text" 
                name="search" 
                placeholder="Search by Review ID, User, Product, Comment, Rating..."
                value="<?php echo htmlspecialchars($search); ?>"
            >
            <button type="submit">Search</button>
        </form>

        <h2>All Reviews</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Product</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_array($stmt, OCI_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['REVIEWID']); ?></td>
                    <td><?php echo htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']); ?></td>
                    <td><?php echo htmlspecialchars($row['PRODUCTNAME']); ?></td>
                    <td class="stars">
                        <?php echo str_repeat('★', $row['RATING']) . str_repeat('☆', 5 - $row['RATING']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['REVIEWCOMMENT']); ?></td>
                    <td><?php echo htmlspecialchars($row['REVIEWDATE']); ?></td>
                    <td class="actions">
                        <a href="edit_review.php?id=<?php echo $row['REVIEWID']; ?>">Edit</a>
                        <a href="delete_review.php?id=<?php echo $row['REVIEWID']; ?>" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="../index.php" class="back-btn">← Back to Menu</a>
    </div>
</body>
</html>
<?php oci_close($conn); ?>
