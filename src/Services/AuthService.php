<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use PDO;

class AuthService
{
    private AuthRepository $repository;

    public function __construct(PDO $pdo)
    {
        $this->repository = new AuthRepository($pdo);
    }

    public function findUserByEmail(string $email): ?array
    {
        return $this->repository->findUserByEmail($email);
    }

    public function verifyFirstAccessPassword(array $user, string $password): bool
    {
        $temporaryPassword = (string) ($user['temporary_password'] ?? '');
        if ($temporaryPassword === '') {
            return false;
        }

        if (!empty($user['temporary_password_expires_at'])) {
            $expires = strtotime((string) $user['temporary_password_expires_at']);
            if ($expires !== false && $expires < time()) {
                return false;
            }
        }

        if (password_get_info($temporaryPassword)['algo'] !== null) {
            return password_verify($password, $temporaryPassword);
        }

        if (!hash_equals($temporaryPassword, $password)) {
            return false;
        }

        $this->repository->updateTemporaryPasswordHash((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        return true;
    }

    public function createPasswordResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $this->repository->invalidateOpenResetTokens($userId);
        $this->repository->createPasswordResetToken($userId, $tokenHash);

        return $token;
    }

    public function findValidPasswordResetToken(string $token): ?array
    {
        return $this->repository->findValidResetByTokenHash(hash('sha256', $token));
    }

    public function updatePasswordWithToken(string $token, string $newPassword): bool
    {
        $reset = $this->findValidPasswordResetToken($token);
        if (!$reset) {
            return false;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->repository->updatePasswordByUserId((int) $reset['user_id'], $passwordHash);
        $this->repository->invalidateOpenResetTokens((int) $reset['user_id']);
        return true;
    }
}

