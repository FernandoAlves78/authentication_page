<?php
require_once __DIR__ . '/../../src/Support/connection.php';

try {
    $pdo = getPdo();

    $email = 'demo@example.com';
    $name = 'Usuario Demo';
    $plainPassword = 'Demo1234!';

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        echo "Usuario demo ja existe." . PHP_EOL;
        exit;
    }

    $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (email, password_hash, name) VALUES (:email, :password_hash, :name)');
    $insert->execute([
        ':email' => $email,
        ':password_hash' => $passwordHash,
        ':name' => $name,
    ]);

    echo "Usuario demo criado com sucesso." . PHP_EOL;
} catch (PDOException $e) {
    echo "Erro ao criar usuario demo: " . $e->getMessage() . PHP_EOL;
}

