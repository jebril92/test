<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function generate_verification_code($length = 6) {
    $characters = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $code;
}

function send_verification_email($to_email, $username, $verification_code) {
    require 'vendor/autoload.php';

    $smtp_host = 'smtp.gmail.com';
    $smtp_port = 465;
    $smtp_username = 'jebrilhocine@gmail.com';
    $smtp_password = 'wlff funy povv cpdj';
    $smtp_from_name = 'URLink Service';
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $smtp_port;
        $mail->setFrom($smtp_username, $smtp_from_name);
        $mail->addAddress($to_email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code for URLink Account';

        $mail->Body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4a69bd; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .code { font-size: 24px; letter-spacing: 5px; text-align: center; padding: 20px; margin: 20px 0; background-color: #f8f9fa; border: 1px dashed #ccc; font-weight: bold; }
                .footer { margin-top: 20px; font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Welcome to URLink!</h2>
                </div>
                <div class="content">
                    <p>Hello ' . htmlspecialchars($username) . ',</p>
                    <p>Thank you for registering with URLink. To complete your registration, please use the verification code below:</p>
                    <div class="code">' . $verification_code . '</div>
                    <p>Please enter this code on the verification page to activate your account.</p>
                    <p>This code will expire in 24 hours.</p>
                    <p>If you did not create an account with URLink, please ignore this email.</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' URLink. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';

        // if the user have not the html enabled
        $mail->AltBody = "Hello $username,\n\n"
            . "Thank you for registering with URLink. To complete your registration, please use the verification code below:\n\n"
            . "$verification_code\n\n"
            . "Please enter this code on the verification page to activate your account.\n\n"
            . "This code will expire in 24 hours.\n\n"
            . "If you did not create an account with URLink, please ignore this email.\n\n"
            . "© " . date('Y') . " URLink. All rights reserved.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
