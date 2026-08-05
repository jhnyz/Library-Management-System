<?php


require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'pay') {
        $paymentId = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
        $amount    = isset($_POST['amount']) ? intval($_POST['amount']) : 0;

        if ($paymentId > 0 && $amount > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE fine_payments SET payment = ? WHERE payment_id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $amount, $paymentId);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Payment of {$amount} recorded successfully!";
            } else {
                $errors[] = "Failed to record payment: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Invalid payment information.";
        }

    } elseif ($action === 'delete') {
        $paymentId = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;

        if ($paymentId > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM fine_payments WHERE payment_id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $paymentId);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Payment record deleted successfully!";
            } else {
                $errors[] = "Failed to delete payment: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Invalid payment ID.";
        }
    }
}

$query = "
    SELECT fp.payment_id, fp.book_id, fp.payment,
           b.title AS book_title, b.dl AS due_date, b.stat AS status
    FROM fine_payments fp
    LEFT JOIN book b ON fp.book_id = b.book_id
    ORDER BY fp.payment_id DESC
";
$result = mysqli_query($conn, $query);
$fines = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $fines[] = $row;
    }
}

$totalFines = 0;
foreach ($fines as $fine) {
    $totalFines += $fine['payment'];
}

mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fine Payments</title>
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
        .summary {
            background: #e9ecef;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .summary strong {
            font-size: 18px;
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
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-pay {
            background: #28a745;
            color: #fff;
        }
        .btn-delete {
            background: #dc3545;
            color: #fff;
        }
        .amount-input {
            width: 80px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
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
        <h2>Fine Payments</h2>


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
            <span>Total Outstanding Fines</span>
            <strong>$<?php echo number_format($totalFines, 2); ?></strong>
        </div>


        <?php if (count($fines) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Book Title</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Fine Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fines as $fine): ?>
                        <tr>
                            <td><?php echo $fine['payment_id']; ?></td>
                            <td><?php echo htmlspecialchars($fine['book_title'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($fine['due_date']); ?></td>
                            <td><?php echo htmlspecialchars($fine['status']); ?></td>
                            <td>$<?php echo number_format($fine['payment'], 2); ?></td>
                            <td>
                                <form action="fines.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="pay">
                                    <input type="hidden" name="payment_id" value="<?php echo $fine['payment_id']; ?>">
                                    <input type="number" name="amount" class="amount-input" min="0" value="<?php echo $fine['payment']; ?>" required>
                                    <button type="submit" class="btn btn-pay">Pay</button>
                                </form>
                                <form action="fines.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this payment record?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="payment_id" value="<?php echo $fine['payment_id']; ?>">
                                    <button type="submit" class="btn btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty">No fine payments recorded yet.</div>
        <?php endif; ?>
    </div>
</body>
</html>



