<?php



define('DB_SERVER', '127.0.0.1');   
define('DB_USERNAME', 'root');      
define('DB_PASSWORD', '');          
define('DB_NAME', 'library_db');    

// Establish the database connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if (!$conn) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Set the character set to UTF-8 for proper handling of text
mysqli_set_charset($conn, "utf8mb4");


?>
