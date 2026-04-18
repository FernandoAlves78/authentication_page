<?php

class Config
{
    // Database configuration
    const host = 'localhost';
    const db_name = 'auth_app';
    const db_username = 'root';
    const db_password = ''; // Use env vars for sensitive data in production.

    // Mail defaults
    const mail_from_name = 'Auth App';
    const mail_from_address = 'noreply@localhost';

    /** true = SMTP (MailHog: 127.0.0.1:1025). false = mail() do PHP */
    const mail_smtp_enabled = true;
    const mail_smtp_host = '127.0.0.1';
    const mail_smtp_port = 1025;

    /** Ex.: authentication_page/public se o site for http://host/authentication_page/public/ — vazio = auto */
    const web_public_path = '';

    public static function baseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $hostHeader = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $hostHeader;
    }
}

