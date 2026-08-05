<?php


require 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = isset($_POST['book-title']) ? trim($_POST['book-title']) : '';
    $period = isset($_POST['period']) ? intval($_POST['period']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Borrowed';
    $fine   = isset($_POST['fine']) ? intval($_POST['fine']) : 0;

    $errors = [];

    if ($title === '') {
        $errors[] = "Book title is required.";
    }

    $days = 0;
    if ($period === 3) {
        $days = 3;
    } elseif ($period === 7) {
        $days = 7;
    } elseif ($period === 14) {
        $days = 14;
    } else {
        $errors[] = "Please select a valid borrowing period.";
    }

    if (empty($errors)) {
        $dueDate = date('Y-m-d', strtotime("+{$days} days"));

        $stmt = mysqli_prepare($conn, "INSERT INTO book (title, dl, stat) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $title, $dueDate, $status);

        if (mysqli_stmt_execute($stmt)) {
            $newBookId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            if ($fine > 0) {
                $stmtFine = mysqli_prepare($conn, "INSERT INTO fine_payments (book_id, payment) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmtFine, 'ii', $newBookId, $fine);

                if (!mysqli_stmt_execute($stmtFine)) {
                    $errors[] = "Book recorded, but failed to save fine: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmtFine);
            }

            if (empty($errors)) {
                $success = "Book \"" . htmlspecialchars($title) . "\" borrowed successfully! Due date: {$dueDate}.";
                $title = $period = $status = $fine = "";
            }
        } else {
            $errors[] = "Failed to save book: " . mysqli_error($conn);
            mysqli_stmt_close($stmt);
        }
    }
}


mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing Form</title>
    
</head>
<body>
    <div class="container">
        <h2>Borrowing Form</h2>


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


        <form action="borrow.php" method="POST">
            <label>Book Title</label>
            <input type="text" name="book-title" class="form-control" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>


            <label>Borrowing Period</label>
            <select name="period" class="form-control" required>
                <option value="">-- Select Period --</option>
                <option value="3" <?php echo (isset($period) && $period == 3) ? 'selected' : ''; ?>>3 Days</option>
                <option value="7" <?php echo (isset($period) && $period == 7) ? 'selected' : ''; ?>>7 Days</option>
                <option value="14" <?php echo (isset($period) && $period == 14) ? 'selected' : ''; ?>>14 Days</option>
            </select><br><br>


            <label>Status</label>
            <select name="status" class="form-control" required>
                <?php
                $statuses = ['Borrowed', 'Overdue', 'Returned'];
                foreach ($statuses as $s) {
                    $sel = (isset($status) && $status === $s) ? 'selected' : '';
                    echo "<option value=\"{$s}\" {$sel}>{$s}</option>";
                }
                ?>
            </select><br><br>


            <label>Fine Amount</label>
            <input type="number" name="fine" class="form-control" min="0" value="<?php echo isset($fine) && $fine !== "" ? htmlspecialchars($fine) : '0'; ?>"><br><br>


            <button type="submit" class="btn">Save</button><br><br>
        </form>
    </div>
</body>
</html>
