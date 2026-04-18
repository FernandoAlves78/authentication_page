<?php
require_once __DIR__ . '/../src/Security/auth_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

handleResetPasswordRequest();

