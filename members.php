<?php
/**
 * members.php - Handles library members
 * 
 * Displays all members and allows adding new members linked to books.
 * Interacts with the `members` and `book` tables.
 */

require 'config.php';

// Handle form submission to add a new member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $stud   = isset($_POST['stud']) ? trim($_POST['stud']) : '';
        $pin    = isset($_POST['pin']) ? intval($_POST['pin']) : 0;
        $bookId = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;

        if ($stud === '') {
            $errors[] = "Student name is required.";
        }
        if ($pin <= 0) 
            $errors[] = "Please enter a valid PIN.";
        }
        if ($bookId <= 0) {
            $errors[] = "Please select a book.";
        }

        if (empty($errors)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO members (book_id, stud, pin) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isi', $bookId, $stud, $pin);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Member \"" . htmlspecialchars($stud) . "\" added successfully!";
            } else {
                $errors[] = "Failed to add member: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }

    } elseif ($action === 'delete') {
        $memberId = isset($_POST['member_id']) ? intval($_POST['member_id']) : 0;

        if ($memberId > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM members WHERE member_id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $memberId);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Member deleted successfully!";
            } else {
                $errors[] = "Failed to delete member: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Invalid member ID.";
        }
    }


// Fetch all members joined with book titles
$membersQuery = "
    SELECT m.member_id, m.book_id, m.stud, m.pin,
           b.title AS book_title, b.stat AS status
    FROM members m
    LEFT JOIN book b ON m.book_id = b.book_id
    ORDER BY m.member_id DESC
";
$membersResult = mysqli_query($conn, $membersQuery);
$members = [];
if ($membersResult) {
    while ($row = mysqli_fetch_assoc($membersResult)) {
        $members[] = $row;
    }
}

// Fetch available books for the dropdown
$booksQuery = "SELECT book_id, title FROM book ORDER BY title";
$booksResult = mysqli_query($conn, $booksQuery);
$books = [];
if ($booksResult) {
    while ($row = mysqli_fetch_assoc($booksResult)) {
        $books[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Members</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f4f6f9;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 900px;
        }
        h2 {
            text-align: center;
            margin-top: 0;
        }
        .back {
            color: #007bff;
            text-decoration: none;
        }
        .member-form {
            background: #e9ecef;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .member-form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: #fff;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-add {
            background: #007bff;
            color: #fff;
            margin-top: 15px;
            width: 100%;
        }
        .btn-delete {
            background: #dc3545;
            color: #fff;
            padding: 6px 12px;
        }
        .alert {
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .empty {
            text-align: center;
            color: #888;
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a class="back" href="Borrowing Book.html">&larr; Back to Borrowing Form</a>
        <h2>Library Members</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="summary">
            <span>Total Members</span>
            <strong><?php echo count($members); ?></strong>
        </div>

        <div class="member-form">
            <h3>Add New Member</h3>
            <form action="members.php" method="POST">
                <input type="hidden" name="action" value="add">
                <label>Student Name</label>
                <input type="text" name="stud" class="form-control" required>

                <label>PIN</label>
                <input type="number" name="pin" class="form-control" min="0" required>

                <label>Borrowed Book</label>
                <select name="book_id" class="form-control" required>
                    <option value="">-- Select Book --</option>
                    <?php foreach ($books as $book): ?>
                        <option value="<?php echo $book['book_id']; ?>"><?php echo htmlspecialchars($book['title']); ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-add">Add Member</button>
            </form>
        </div>

        <?php if (count($members) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Student Name</th>
                        <th>PIN</th>
                        <th>Book</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo $member['member_id']; ?></td>
                            <td><?php echo htmlspecialchars($member['stud']); ?></td>
                            <td><?php echo htmlspecialchars($member['pin']); ?></td>
                            <td><?php echo htmlspecialchars($member['book_title'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($member['status'] ?? 'N/A'); ?></td>
                            <td>
                                <form action="members.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this member?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="member_id" value="<?php echo $member['member_id']; ?>">
                                    <button type="submit" class="btn btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty">No members recorded yet.</div>
        <?php endif; ?>
    </div>
</body>
</html>
