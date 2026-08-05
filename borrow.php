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

    
}


mysqli_close($conn);
?>

