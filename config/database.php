<?php
/**
 * Database Configuration
 */

return [
    'host' => 'localhost',
    'dbname' => 'tabulation_system',
    'username' => 'root',
    'password' => '1412',
    'charset' => 'utf8mb4',
    'port' => 3306,
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];



