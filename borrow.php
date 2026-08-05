<?php


require 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $title  = isset($_POST['book-title']) ? trim($_POST['book-title']) : '';
    $period = isset($_POST['period']) ? intval($_POST['period']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Borrowed';
    $fine   = isset($_POST['fine']) ? intval($_POST['fine']) : 0;

   


mysqli_close($conn);
?>

