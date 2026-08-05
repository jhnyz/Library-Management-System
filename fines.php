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
