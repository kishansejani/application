<?php

$host = '127.0.0.1';
$port = 3306;
$db   = 'testing_pro';
$user = 'root';
$pass = 'Decent@2018$$';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die("Database Connection failed: " . $e->getMessage() . "\n");
}

$output = "-- MySQL Database Dump for testing_pro\n";
$output .= "-- Generated on " . date('Y-m-d H:i:s') . "\n";
$output .= "-- Host: {$host}    Database: {$db}\n";
$output .= "-- ------------------------------------------------------\n\n";
$output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
    $createTableSql = $createStmt['Create Table'] ?? '';

    $output .= "--\n-- Table structure for table `{$table}`\n--\n\n";
    $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $output .= $createTableSql . ";\n\n";

    // Data
    $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        $output .= "--\n-- Dumping data for table `{$table}`\n--\n\n";
        $columns = array_keys($rows[0]);
        $columnsSql = implode('`, `', $columns);

        foreach ($rows as $row) {
            $values = array_map(function($val) use ($pdo) {
                if ($val === null) return 'NULL';
                return $pdo->quote($val);
            }, array_values($row));
            $valuesSql = implode(', ', $values);
            $output .= "INSERT INTO `{$table}` (`{$columnsSql}`) VALUES ({$valuesSql});\n";
        }
        $output .= "\n";
    }
}

$output .= "SET FOREIGN_KEY_CHECKS=1;\n";

file_put_contents(__DIR__ . '/database/testing_pro.sql', $output);
file_put_contents(__DIR__ . '/testing_pro.sql', $output);

echo "Successfully exported testing_pro database to database/testing_pro.sql and testing_pro.sql (" . count($tables) . " tables)!\n";
