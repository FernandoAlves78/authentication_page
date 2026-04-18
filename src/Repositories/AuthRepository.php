<?php

namespace App\Repositories;

use PDO;

class AuthRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function updateTemporaryPasswordHash(int $userId, string $temporaryPasswordHash): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET temporary_password = :temporary_password WHERE id = :id');
        $stmt->execute([
            ':temporary_password' => $temporaryPasswordHash,
            ':id' => $userId,
        ]);
    }

    public function invalidateOpenResetTokens(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE password_resets SET used = 1 WHERE user_id = :user_id AND used = 0');
        $stmt->execute([':user_id' => $userId]);
    }

    public function createPasswordResetToken(int $userId, string $tokenHash): void
    {
        // Usa sempre o relógio do MySQL para expires_at — evita token “já expirado” quando
        // timezone do PHP e do servidor MySQL não coincidem.
        $stmt = $this->pdo->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at, used)
             VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 30 MINUTE), 0)'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':token_hash' => $tokenHash,
        ]);
    }

    public function findValidResetByTokenHash(string $tokenHash): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT pr.*, u.email
             FROM password_resets pr
             JOIN users u ON u.id = pr.user_id
             WHERE pr.token_hash = :token_hash
               AND pr.used = 0
               AND pr.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([':token_hash' => $tokenHash]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function updatePasswordByUserId(int $userId, string $passwordHash): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET
                password_hash = :password_hash,
                temporary_password = "",
                must_reset_password = 0,
                temporary_password_expires_at = NULL
             WHERE id = :user_id'
        );
        $stmt->execute([
            ':password_hash' => $passwordHash,
            ':user_id' => $userId,
        ]);
    }
}

