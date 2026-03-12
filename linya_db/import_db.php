<?php
/**
 * import_db.php
 * Usage (CLI): php linya_db/import_db.php
 * Usage (browser): http://localhost/LINYA/linya_db/import_db.php
 *
 * This script will create the `linya1_db` database (if missing) and import
 * the SQL dump file `linya1_db.sql` located at the repository root.
 *
 * IMPORTANT: Run this only in a local/dev environment. Do NOT run on production
 * without reviewing the SQL file and backups.
 */

ini_set('memory_limit','-1');
set_time_limit(0);
error_reporting(E_ALL);

$server = 'localhost';
$user   = 'root';
$pass   = '';
$dbname  = 'linya1_db';
$sqlFile = __DIR__ . '/../linya1_db.sql';

function out($msg) {
    if (php_sapi_name() === 'cli') {
        echo $msg . PHP_EOL;
    } else {
        echo nl2br(htmlspecialchars($msg)) . '<br>';
    }
}

out("Import started. SQL file: $sqlFile");

if (!file_exists($sqlFile)) {
    out("ERROR: SQL file not found: $sqlFile");
    exit(1);
}

$mysqli = new mysqli($server, $user, $pass);
if ($mysqli->connect_error) {
    out("ERROR: MySQL connection failed: " . $mysqli->connect_error);
    exit(1);
}

// Create database if not exists
if (!$mysqli->select_db($dbname)) {
    out("Database '$dbname' not found — creating...");
    $create = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if (!$mysqli->query($create)) {
        out("ERROR: Could not create database: " . $mysqli->error);
        exit(1);
    }
    $mysqli->select_db($dbname);
} else {
    out("Using existing database '$dbname'.");
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    out("ERROR: Failed to read SQL file.");
    exit(1);
}

out("Executing SQL (this may take a while)...");

if (!$mysqli->multi_query($sql)) {
    out("ERROR: multi_query failed: " . $mysqli->error);
    exit(1);
}

// Flush multi_query results
do {
    if ($res = $mysqli->store_result()) {
        $res->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

if ($mysqli->errno) {
    out("Completed with errors: " . $mysqli->error);
    exit(1);
}

out("Import completed successfully.");
$mysqli->close();

echo PHP_SAPI === 'cli' ? "Done.\n" : "Done.";
