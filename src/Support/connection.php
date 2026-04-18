<?php

require_once __DIR__ . '/../../config/config.php';

$host = Config::host;
$db_name = Config::db_name;
$db_username = Config::db_username;
$db_password = Config::db_password;

function getPdo(): PDO
{
    global $host, $db_name, $db_username, $db_password;

    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $db_name);
        $pdo = new PDO($dsn, $db_username, $db_password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $pdo;
}

