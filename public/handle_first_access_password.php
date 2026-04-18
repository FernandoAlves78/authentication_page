<?php
require_once __DIR__ . '/../src/Security/auth_functions.php';

startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    header('Location: dashboard.php?pw_error=1&pw_reason=csrf');
    exit;
}

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
if ($userId <= 0) {
    header('Location: index.php');
    exit;
}

$newPassword = (string) ($_POST['password'] ?? '');
$newPasswordConfirm = (string) ($_POST['password_confirm'] ?? '');

if ($newPassword === '' || $newPasswordConfirm === '') {
    header('Location: dashboard.php?pw_error=1&pw_reason=empty');
    exit;
}
if ($newPassword !== $newPasswordConfirm) {
    header('Location: dashboard.php?pw_error=1&pw_reason=mismatch');
    exit;
}
if (strlen($newPassword) < MIN_PASSWORD_LENGTH) {
    header('Location: dashboard.php?pw_error=1&pw_reason=length');
    exit;
}

try {
    $pdo = getPdo();
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'UPDATE users SET
            password_hash = :password_hash,
            temporary_password = "",
            must_reset_password = 0,
            temporary_password_expires_at = NULL
         WHERE id = :user_id
           AND must_reset_password = 1'
    );
    $stmt->execute([
        ':password_hash' => $passwordHash,
        ':user_id' => $userId,
    ]);

    session_regenerate_id(true);
    header('Location: dashboard.php?updated=1');
    exit;
} catch (Throwable $e) {
    error_log('First access password update failed: ' . $e->getMessage());
    header('Location: dashboard.php?pw_error=1&pw_reason=unexpected');
    exit;
}

