<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP/MySQL Diagnostic Tool</h1>";

$servername = "127.0.0.1";
$username = "root";
$password = "";

// 1. Test Connection
echo "<h3>1. Testing MySQL Connection...</h3>";
$conn = mysqli_connect($servername, $username, $password);

if (!$conn) {
    die("<span style='color:red;'>❌ Connection failed: " . mysqli_connect_error() . "</span>");
}
echo "<span style='color:green;'>✅ Connected successfully to MySQL!</span><br>";

// 2. Check Database
echo "<h3>2. Checking for database 'my_db'...</h3>";
$db_check = mysqli_select_db($conn, 'my_db');

if (!$db_check) {
    echo "<span style='color:red;'>❌ Database 'my_db' does not exist.</span><br>";
    echo "Attempting to create it...<br>";
    if (mysqli_query($conn, "CREATE DATABASE my_db")) {
        echo "<span style='color:green;'>✅ Database 'my_db' created successfully!</span><br>";
        mysqli_select_db($conn, 'my_db');
    } else {
        echo "<span style='color:red;'>❌ Could not create database: " . mysqli_error($conn) . "</span><br>";
    }
} else {
    echo "<span style='color:green;'>✅ Database 'my_db' found!</span><br>";
}

// 3. Check Tables
echo "<h3>3. Checking for required tables...</h3>";
$tables = array("users");
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<span style='color:green;'>✅ Table '$table' exists.</span><br>";
    } else {
        echo "<span style='color:red;'>❌ Table '$table' is missing.</span><br>";
        if ($table == "users") {
            echo "Creating 'users' table...<br>";
            $sql = "CREATE TABLE users (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
            if (mysqli_query($conn, $sql)) {
                echo "<span style='color:green;'>✅ Table 'users' created successfully.</span><br>";
            } else {
                echo "<span style='color:red;'>❌ Error creating table: " . mysqli_error($conn) . "</span><br>";
            }
        }
    }
}

echo "<h3>Diagnostic Complete.</h3>";
echo "Try accessing <a href='main.php'>main.php</a> now.";
mysqli_close($conn);
?>
