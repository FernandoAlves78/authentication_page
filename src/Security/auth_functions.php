<?php
require_once __DIR__ . '/../Support/connection.php';
require_once __DIR__ . '/../Support/i18n.php';
require_once __DIR__ . '/../Repositories/AuthRepository.php';
require_once __DIR__ . '/../Services/AuthService.php';

use App\Services\AuthService;

const MIN_PASSWORD_LENGTH = 8;
const LOGIN_RATE_LIMIT_WINDOW_SECONDS = 900;
const LOGIN_RATE_LIMIT_MAX_ATTEMPTS = 5;
const RECOVERY_RATE_LIMIT_MAX_ATTEMPTS = 3;

function authService(): AuthService
{
    static $service = null;
    if ($service === null) {
        $service = new AuthService(getPdo());
    }
    return $service;
}

/**
 * Segmento de URL até à pasta public (ex.: /authentication_page/public). Vazio se o vhost já aponta para public.
 */
function getWebPublicPath(): string
{
    $configured = trim((string) Config::web_public_path);
    if ($configured !== '') {
        return '/' . trim(str_replace('\\', '/', $configured), '/');
    }
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($scriptName === '' || $scriptName === '/') {
        return '';
    }
    $dir = dirname(str_replace('\\', '/', $scriptName));
    if ($dir === '/' || $dir === '.') {
        return '';
    }

    return $dir;
}

function buildPasswordResetUrl(string $token): string
{
    $base = rtrim(Config::baseUrl(), '/');
    $path = getWebPublicPath();

    return $base . $path . '/reset_password.php?token=' . rawurlencode($token);
}

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) === '443');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function getCsrfToken(): string
{
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    startSecureSession();
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (!is_string($token) || $token === '' || !is_string($sessionToken) || $sessionToken === '') {
        return false;
    }
    return hash_equals($sessionToken, $token);
}

function getClientIp(): string
{
    return trim((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

function isRateLimited(string $action, string $identifier, int $maxAttempts): bool
{
    $pdo = getPdo();
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM auth_attempts
         WHERE action = :action
           AND identifier = :identifier
           AND attempted_at >= (NOW() - INTERVAL :window SECOND)'
    );
    $stmt->bindValue(':action', $action, PDO::PARAM_STR);
    $stmt->bindValue(':identifier', $identifier, PDO::PARAM_STR);
    $stmt->bindValue(':window', LOGIN_RATE_LIMIT_WINDOW_SECONDS, PDO::PARAM_INT);
    $stmt->execute();
    return (int) $stmt->fetchColumn() >= $maxAttempts;
}

function registerFailedAttempt(string $action, string $identifier): void
{
    $pdo = getPdo();
    $stmt = $pdo->prepare(
        'INSERT INTO auth_attempts (action, identifier, ip_address)
         VALUES (:action, :identifier, :ip_address)'
    );
    $stmt->execute([
        ':action' => $action,
        ':identifier' => $identifier,
        ':ip_address' => getClientIp(),
    ]);
}

function clearAttempts(string $action, string $identifier): void
{
    $pdo = getPdo();
    $stmt = $pdo->prepare(
        'DELETE FROM auth_attempts
         WHERE action = :action
           AND identifier = :identifier'
    );
    $stmt->execute([
        ':action' => $action,
        ':identifier' => $identifier,
    ]);
}

function findUserByEmail(string $email): ?array
{
    return authService()->findUserByEmail($email);
}

function verifyFirstAccessPassword(array $user, string $password): bool
{
    return authService()->verifyFirstAccessPassword($user, $password);
}

function findAndVerifyUser(string $email, string $password): ?array
{
    $user = findUserByEmail($email);
    if (!$user) {
        return null;
    }
    if ((int) $user['must_reset_password'] === 1) {
        return verifyFirstAccessPassword($user, $password) ? $user : null;
    }
    return password_verify($password, (string) $user['password_hash']) ? $user : null;
}

function handleLoginRequest(): void
{
    startSecureSession();
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        header('Location: login_failed.php?reason=csrf');
        exit;
    }
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $identifier = 'login:' . $email . '|' . getClientIp();

    if ($email === '' || $password === '') {
        registerFailedAttempt('login', $identifier);
        header('Location: login_failed.php?reason=validation');
        exit;
    }

    if (isRateLimited('login', $identifier, LOGIN_RATE_LIMIT_MAX_ATTEMPTS)) {
        registerFailedAttempt('login', $identifier);
        header('Location: login_failed.php?reason=rate_limit');
        exit;
    }

    $user = findAndVerifyUser($email, $password);
    if (!$user) {
        registerFailedAttempt('login', $identifier);
        header('Location: login_failed.php');
        exit;
    }

    clearAttempts('login', $identifier);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    header('Location: dashboard.php');
    exit;
}

function createPasswordResetToken(int $userId): string
{
    return authService()->createPasswordResetToken($userId);
}

function sendPasswordResetEmail(string $email, string $token): void
{
    $resetLink = buildPasswordResetUrl($token);
    $subject = t('email.reset_subject');
    $team = htmlspecialchars(Config::mail_from_name, ENT_QUOTES, 'UTF-8');
    $plainMessage = t('email.salutation') . "\n\n";
    $plainMessage .= t('email.reset_plain_opening') . "\n";
    $plainMessage .= t('email.reset_plain_instruction') . "\n\n";
    $plainMessage .= $resetLink . "\n\n";
    $plainMessage .= t('email.reset_plain_ignore') . "\n\n— " . Config::mail_from_name . "\n";

    $safeHref = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');
    $safeLinkText = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');
    $langAttr = htmlspecialchars(html_lang_attribute(), ENT_QUOTES, 'UTF-8');
    $htmlMessage = '<!DOCTYPE html><html lang="' . $langAttr . '"><head><meta charset="UTF-8"></head><body>';
    $htmlMessage .= '<p>' . htmlspecialchars(t('email.salutation'), ENT_QUOTES, 'UTF-8') . '</p>';
    $htmlMessage .= '<p>' . htmlspecialchars(t('email.reset_plain_opening'), ENT_QUOTES, 'UTF-8') . '</p>';
    $htmlMessage .= '<p><a href="' . $safeHref . '">' . htmlspecialchars(t('email.reset_html_button'), ENT_QUOTES, 'UTF-8') . '</a></p>';
    $htmlMessage .= '<p style="font-size:12px;color:#555">' . htmlspecialchars(t('email.reset_html_hint'), ENT_QUOTES, 'UTF-8') . '</p>';
    $htmlMessage .= '<p style="word-break:break-all;font-size:12px">' . $safeLinkText . '</p>';
    $htmlMessage .= '<p>' . htmlspecialchars(t('email.reset_html_ignore'), ENT_QUOTES, 'UTF-8') . '</p>';
    $htmlMessage .= '<p>— ' . $team . '</p>';
    $htmlMessage .= '</body></html>';

    $headersHtml = [
        'From: ' . Config::mail_from_name . ' <' . Config::mail_from_address . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
    ];

    if (Config::mail_smtp_enabled) {
        require_once __DIR__ . '/../Support/smtp_mail.php';
        $sent = sendMultipartAlternativeMailViaSmtp(
            $email,
            $subject,
            $plainMessage,
            $htmlMessage,
            Config::mail_from_name,
            Config::mail_from_address
        );
        if (!$sent) {
            error_log('Envio SMTP da recuperacao de senha falhou; tentativa com mail().');
            mail($email, $subject, $htmlMessage, implode("\r\n", $headersHtml));
        }
        return;
    }

    mail($email, $subject, $htmlMessage, implode("\r\n", $headersHtml));
}

function handleForgotPasswordRequest(): void
{
    startSecureSession();
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        header('Location: forgot_password.php?error=csrf');
        exit;
    }
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $identifier = 'recovery:' . $email . '|' . getClientIp();
    if ($email !== '' && !isRateLimited('recovery', $identifier, RECOVERY_RATE_LIMIT_MAX_ATTEMPTS)) {
        $user = findUserByEmail($email);
        if ($user) {
            sendPasswordResetEmail($email, createPasswordResetToken((int) $user['id']));
        }
    } else {
        registerFailedAttempt('recovery', $identifier);
    }
    $fh = htmlspecialchars(t('forgot_sent.page_title'), ENT_QUOTES, 'UTF-8');
    $fh2 = htmlspecialchars(t('forgot_sent.heading'), ENT_QUOTES, 'UTF-8');
    $msg = htmlspecialchars(t('forgot_sent.message'), ENT_QUOTES, 'UTF-8');
    $btn = htmlspecialchars(t('forgot_sent.back_login'), ENT_QUOTES, 'UTF-8');
    $hl = htmlspecialchars(html_lang_attribute(), ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="' . $hl . '"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>'
        . $fh . '</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" /></head><body class="bg-light d-flex align-items-center min-vh-100"><div class="container"><div class="row justify-content-center"><div class="col-md-6 col-lg-4"><div class="card shadow-sm"><div class="card-body"><h1 class="h4 mb-3 text-center">'
        . $fh2 . '</h1><p class="mb-3">' . $msg . '</p><div class="text-center mt-3"><a href="../index.php" class="btn btn-primary">'
        . $btn . '</a></div></div></div></div></div></div></body></html>';
}

function findValidPasswordResetToken(string $token): ?array
{
    return authService()->findValidPasswordResetToken($token);
}

function updatePasswordWithToken(string $token, string $newPassword): bool
{
    $pdo = getPdo();
    $pdo->beginTransaction();
    try {
        if (!authService()->updatePasswordWithToken($token, $newPassword)) {
            $pdo->rollBack();
            return false;
        }
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Password reset error: ' . $e->getMessage());
        return false;
    }
}

function handleResetPasswordRequest(): void
{
    startSecureSession();
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        header('Location: reset_failed.php?reason=csrf');
        exit;
    }
    $token = (string) ($_POST['token'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
    $identifier = 'recovery_confirm:' . getClientIp();

    if (isRateLimited('recovery_confirm', $identifier, RECOVERY_RATE_LIMIT_MAX_ATTEMPTS)) {
        registerFailedAttempt('recovery_confirm', $identifier);
        header('Location: reset_failed.php?reason=rate_limit');
        exit;
    }

    if (
        $token === ''
        || $password === ''
        || $passwordConfirm === ''
        || strlen($password) < MIN_PASSWORD_LENGTH
        || $password !== $passwordConfirm
    ) {
        registerFailedAttempt('recovery_confirm', $identifier);
        header('Location: reset_failed.php?reason=validation');
        exit;
    }

    if (!updatePasswordWithToken($token, $password)) {
        registerFailedAttempt('recovery_confirm', $identifier);
        header('Location: reset_failed.php');
        exit;
    }

    clearAttempts('recovery_confirm', $identifier);
    session_regenerate_id(true);
    $_SESSION = [];
    header('Location: reset_success.php');
    exit;
}

