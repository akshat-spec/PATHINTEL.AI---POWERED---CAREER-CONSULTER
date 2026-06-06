<?php
/* Database credentials. Attempt to read from environment variables first 
(for Vercel deployment), and fallback to local defaults if missing. */
define('DB_SERVER', getenv('DB_SERVER') ?: 'db');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'my_db');
 
$link = null;

if (function_exists('mysqli_connect')) {
    try {
        /* Attempt to connect to MySQL database */
        $link = @mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if($link === false){
            $link = null;
        }
    } catch (Throwable $e) {
        $link = null;
    }
}
?>