<?php
// database.php
$servername = "localhost"; // Default for AMPPS
$username = "noozeApplication";        // Default for AMPPS
$password = "nooze10";            // Default for AMPPS (no password)
$dbname = "church_web_app"; // Name of the church database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully"; // Test if the connection is successful
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
