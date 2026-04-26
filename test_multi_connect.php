<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$hosts = ["localhost", "127.0.0.1", "::1", "localhost:3306", "127.0.0.1:3306"];
$user = "root";
$pass = "";

echo "<h1>MySQL Connection Tester</h1>";

foreach ($hosts as $host) {
    echo "<h3>Testing Host: $host</h3>";
    try {
        $conn = @mysqli_connect($host, $user, $pass);
        if ($conn) {
            echo "<span style='color:green;'>✅ SUCCESS! Connected to $host</span><br>";
            mysqli_close($conn);
        } else {
            echo "<span style='color:red;'>❌ FAILED: " . mysqli_connect_error() . "</span><br>";
        }
    } catch (Exception $e) {
        echo "<span style='color:red;'>❌ EXCEPTION: " . $e->getMessage() . "</span><br>";
    }
    echo "<hr>";
}
?>
