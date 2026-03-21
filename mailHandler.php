<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function sendEmailWithAttachment($recipientEmail, $recipientName, $subject, $body, $attachmentPath) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->Port = 587; // Use 465 for SSL or 587 for TLS
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPAuth = true;
        $mail->AuthType = 'XOAUTH2';

        $provider = new Google([
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
        ]);

        $mail->setOAuth(new OAuth([
            'provider'     => $provider,
            'clientId'     => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'refreshToken' => $_ENV['GOOGLE_REFRESH_TOKEN'],
            'userName'     => 'cdpvfinanceoffice@gmail.com',
        ]));

        // Recipients
        $mail->setFrom('cdpvfinanceoffice@gmail.com', 'Colegio de Porta Vaga - Finance Office');
        $mail->addAddress($recipientEmail, $recipientName);
        $mail->addReplyTo('cdpvfinanceoffice@gmail.com', 'Colegio de Porta Vaga - Finance Office');

        $mail->addCustomHeader('List-Unsubscribe', '<mailto:cdpvfinanceoffice@gmail.com>');

        // Attachment
        if (file_exists($attachmentPath)) {
            $mail->addAttachment($attachmentPath);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->XMailer = ' ';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}