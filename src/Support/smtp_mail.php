<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/config.php';

/**
 * Envia e-mail texto via SMTP (adequado para MailHog em localhost:1025).
 */
function sendPlainTextMailViaSmtp(
    string $toAddress,
    string $subject,
    string $plainBody,
    string $fromName,
    string $fromAddress
): bool {
    $host = Config::mail_smtp_host;
    $port = Config::mail_smtp_port;

    $errno = 0;
    $errstr = '';
    $socket = @stream_socket_client(
        sprintf('tcp://%s:%d', $host, $port),
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT
    );

    if ($socket === false) {
        error_log("SMTP: falha ao conectar em {$host}:{$port} — {$errstr} ({$errno})");
        return false;
    }

    stream_set_timeout($socket, 15);

    $readBlock = static function ($sock): string {
        $data = '';
        while (($line = fgets($sock)) !== false) {
            $data .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $codeOk = static function (string $response, array $codes): bool {
        if ($response === '') {
            return false;
        }
        $code = substr($response, 0, 3);
        return in_array($code, $codes, true);
    };

    if (!$codeOk($readBlock($socket), ['220'])) {
        fclose($socket);
        return false;
    }

    fwrite($socket, "EHLO localhost\r\n");
    if (!$codeOk($readBlock($socket), ['250'])) {
        fclose($socket);
        return false;
    }

    fwrite($socket, 'MAIL FROM:<' . $fromAddress . ">\r\n");
    if (!$codeOk($readBlock($socket), ['250'])) {
        fclose($socket);
        return false;
    }

    fwrite($socket, 'RCPT TO:<' . $toAddress . ">\r\n");
    if (!$codeOk($readBlock($socket), ['250'])) {
        fclose($socket);
        return false;
    }

    fwrite($socket, "DATA\r\n");
    if (!$codeOk($readBlock($socket), ['354'])) {
        fclose($socket);
        return false;
    }

    $normBody = str_replace(["\r\n", "\r"], "\n", $plainBody);
    $lines = explode("\n", $normBody);
    $escaped = [];
    foreach ($lines as $line) {
        $escaped[] = (isset($line[0]) && $line[0] === '.') ? '.' . $line : $line;
    }
    $bodyText = implode("\r\n", $escaped);

    $fromNameSafe = preg_replace("/[\r\n]/", '', $fromName);
    $subjectSafe = preg_replace("/[\r\n]/", '', $subject);

    $data = 'From: ' . $fromNameSafe . ' <' . $fromAddress . ">\r\n";
    $data .= 'To: <' . $toAddress . ">\r\n";
    $data .= 'Subject: ' . $subjectSafe . "\r\n";
    $data .= "MIME-Version: 1.0\r\n";
    $data .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $data .= "\r\n";
    $data .= $bodyText . "\r\n.\r\n";

    fwrite($socket, $data);
    if (!$codeOk($readBlock($socket), ['250'])) {
        fclose($socket);
        return false;
    }

    fwrite($socket, "QUIT\r\n");
    $readBlock($socket);
    fclose($socket);

    return true;
}

/**
 * Multipart alternative (texto + HTML) para links clicáveis sem partir linhas em clientes de e-mail.
 */
function sendMultipartAlternativeMailViaSmtp(
    string $toAddress,
    string $subject,
    string $plainBody,
    string $htmlBody,
    string $fromName,
    string $fromAddress
): bool {
    $host = Config::mail_smtp_host;
    $port = Config::mail_smtp_port;

    $errno = 0;
    $errstr = '';
    $socket = @stream_socket_client(
        sprintf('tcp://%s:%d', $host, $port),
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT
    );

    if ($socket === false) {
        error_log("SMTP: falha ao conectar em {$host}:{$port} — {$errstr} ({$errno})");
        return false;
    }

    stream_set_timeout($socket, 15);

    $readBlock = static function ($sock): string {
        $data = '';
        while (($line = fgets($sock)) !== false) {
            $data .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $codeOk = static function (string $response, array $codes): bool {
        if ($response === '') {
            return false;
        }
        $code = substr($response, 0, 3);
        return in_array($code, $codes, true);
    };

    if (!$codeOk($readBlock($socket), ['220'])) {
        fclose($socket);
        return false;
    }

    fwrite($socket, "EHLO localhost\r\n");
    if (!$codeOk($readBlock($socket), ['250'])) {
        fclose($socket);
        return false;
    }

    fwrite($socket, 'MAIL FROM:<' . $fromAddress . ">\r\n");
    if (!$codeOk($readBlock($socket), ['250'])) {
        fclose($socket);
        return false;
    }

    fwrite($socket, 'RCPT TO:<' . $toAddress . ">\r\n");
    if (!$codeOk($readBlock($socket), ['250'])) {
        fclose($socket);
        return false;
    }

    fwrite($socket, "DATA\r\n");
    if (!$codeOk($readBlock($socket), ['354'])) {
        fclose($socket);
        return false;
    }

    $boundary = 'bnd_' . bin2hex(random_bytes(16));
    $fromNameSafe = preg_replace("/[\r\n]/", '', $fromName);
    $subjectSafe = preg_replace("/[\r\n]/", '', $subject);

    $normPlain = str_replace(["\r\n", "\r"], "\n", $plainBody);
    $plainLines = explode("\n", $normPlain);
    $plainEscaped = [];
    foreach ($plainLines as $line) {
        $plainEscaped[] = (isset($line[0]) && $line[0] === '.') ? '.' . $line : $line;
    }
    $plainPart = implode("\r\n", $plainEscaped);

    $normHtml = str_replace(["\r\n", "\r"], "\n", $htmlBody);
    $htmlLines = explode("\n", $normHtml);
    $htmlEscaped = [];
    foreach ($htmlLines as $line) {
        $htmlEscaped[] = (isset($line[0]) && $line[0] === '.') ? '.' . $line : $line;
    }
    $htmlPart = implode("\r\n", $htmlEscaped);

    $payload = 'From: ' . $fromNameSafe . ' <' . $fromAddress . ">\r\n";
    $payload .= 'To: <' . $toAddress . ">\r\n";
    $payload .= 'Subject: ' . $subjectSafe . "\r\n";
    $payload .= "MIME-Version: 1.0\r\n";
    $payload .= 'Content-Type: multipart/alternative; boundary="' . $boundary . "\"\r\n";
    $payload .= "\r\n";
    $payload .= '--' . $boundary . "\r\n";
    $payload .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $payload .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $payload .= $plainPart . "\r\n";
    $payload .= '--' . $boundary . "\r\n";
    $payload .= "Content-Type: text/html; charset=UTF-8\r\n";
    $payload .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $payload .= $htmlPart . "\r\n";
    $payload .= '--' . $boundary . "--\r\n";

    fwrite($socket, $payload . "\r\n.\r\n");
    if (!$codeOk($readBlock($socket), ['250'])) {
        fclose($socket);
        return false;
    }

    fwrite($socket, "QUIT\r\n");
    $readBlock($socket);
    fclose($socket);

    return true;
}
