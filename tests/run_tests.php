<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Security/auth_functions.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function randomEmail(): string
{
    return 'test_' . bin2hex(random_bytes(6)) . '@example.com';
}

function createUser(PDO $pdo, string $email, string $passwordHash, int $mustReset, string $temporaryPassword = ''): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO users (email, password_hash, must_reset_password, temporary_password, temporary_password_expires_at)
         VALUES (:email, :password_hash, :must_reset_password, :temporary_password, :expires_at)'
    );
    $stmt->execute([
        ':email' => $email,
        ':password_hash' => $passwordHash,
        ':must_reset_password' => $mustReset,
        ':temporary_password' => $temporaryPassword,
        ':expires_at' => date('Y-m-d H:i:s', time() + 3600),
    ]);

    return (int) $pdo->lastInsertId();
}

function cleanupUser(PDO $pdo, int $userId): void
{
    $pdo->prepare('DELETE FROM password_resets WHERE user_id = :user_id')->execute([':user_id' => $userId]);
    $pdo->prepare('DELETE FROM users WHERE id = :id')->execute([':id' => $userId]);
}

function testLoginAndResetFlow(): void
{
    // Cobre também validação do token na BD (expires_at relativos a NOW() no MySQL).
    $pdo = getPdo();
    $email = randomEmail();
    $userId = createUser($pdo, $email, password_hash('Senha1234!', PASSWORD_DEFAULT), 0, '');

    try {
        $validUser = findAndVerifyUser($email, 'Senha1234!');
        assertTrue($validUser !== null, 'Login com credenciais validas deveria funcionar.');

        $invalidUser = findAndVerifyUser($email, 'SenhaErrada!');
        assertTrue($invalidUser === null, 'Login com senha invalida deveria falhar.');

        $token = createPasswordResetToken($userId);
        $reset = findValidPasswordResetToken($token);
        assertTrue($reset !== null, 'Token de reset valido deveria ser encontrado.');

        $updated = updatePasswordWithToken($token, 'NovaSenha123!');
        assertTrue($updated, 'Atualizacao de senha com token valido deveria funcionar.');

        $reusedToken = findValidPasswordResetToken($token);
        assertTrue($reusedToken === null, 'Token de reset deve ser uso unico.');
    } finally {
        cleanupUser($pdo, $userId);
    }
}

function testFirstAccessMigration(): void
{
    $pdo = getPdo();
    $email = randomEmail();
    $tempPassword = 'Temp1234!';
    $userId = createUser($pdo, $email, '', 1, $tempPassword);

    try {
        $validUser = findAndVerifyUser($email, $tempPassword);
        assertTrue($validUser !== null, 'Primeiro acesso com senha temporaria valida deveria funcionar.');

        $user = findUserByEmail($email);
        assertTrue($user !== null, 'Usuario deve existir apos teste de primeiro acesso.');
        assertTrue((string) $user['temporary_password'] !== $tempPassword, 'Senha temporaria deve ser migrada para hash.');
        assertTrue(password_verify($tempPassword, (string) $user['temporary_password']), 'Hash da senha temporaria deve ser valido.');
    } finally {
        cleanupUser($pdo, $userId);
    }
}

try {
    testLoginAndResetFlow();
    testFirstAccessMigration();
    echo "Todos os testes passaram.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Falha nos testes: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

