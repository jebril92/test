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
        $mail->Subject = 'Code de vérification pour votre compte URLink';

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>URLink Vérification</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333333;
                    margin: 0;
                    padding: 0;
                    background-color: #f9f9f9;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 20px;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                }
                .header { 
                    background: linear-gradient(135deg, #4a69bd, #3742fa);
                    color: white; 
                    padding: 20px; 
                    text-align: center;
                    border-radius: 8px 8px 0 0;
                }
                .header h2 {
                    margin: 0;
                    padding: 0;
                    font-size: 24px;
                }
                .logo {
                    font-size: 28px;
                    margin-bottom: 10px;
                }
                .content { 
                    padding: 30px; 
                    background-color: #ffffff;
                }
                .code { 
                    font-size: 32px; 
                    letter-spacing: 8px; 
                    text-align: center; 
                    padding: 20px; 
                    margin: 25px 0; 
                    background-color: #f8f9fa; 
                    border: 1px dashed #ccc; 
                    font-weight: bold;
                    border-radius: 6px;
                    color: #3742fa;
                }
                .footer { 
                    margin-top: 30px;
                    text-align: center;
                    font-size: 12px; 
                    color: #777;
                    padding: 20px;
                    border-top: 1px solid #eeeeee;
                }
                p {
                    margin: 15px 0;
                }
                .notice {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 4px;
                    font-size: 14px;
                    border-left: 4px solid #4a69bd;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">URLink</div>
                    <h2>Vérification de compte</h2>
                </div>
                <div class="content">
                    <p>Bonjour ' . htmlspecialchars($username) . ',</p>
                    <p>Merci de vous être inscrit sur URLink. Pour finaliser votre inscription et sécuriser votre compte, veuillez utiliser le code de vérification ci-dessous :</p>
                    <div class="code">' . $verification_code . '</div>
                    <p>Veuillez saisir ce code sur la page de vérification pour activer votre compte.</p>
                    <p>Ce code expirera dans 24 heures pour votre sécurité.</p>
                    <div class="notice">
                        <strong>Note de sécurité :</strong> Si vous n\'avez pas créé de compte sur URLink, veuillez ignorer cet email et contacter notre équipe de support.
                    </div>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' URLink. Tous droits réservés.</p>
                    <p>Ceci est un message automatique, veuillez ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = "Bonjour $username,\n\n"
            . "Merci de vous être inscrit sur URLink. Pour finaliser votre inscription, veuillez utiliser le code de vérification ci-dessous :\n\n"
            . "$verification_code\n\n"
            . "Veuillez saisir ce code sur la page de vérification pour activer votre compte.\n\n"
            . "Ce code expirera dans 24 heures.\n\n"
            . "Si vous n'avez pas créé de compte sur URLink, veuillez ignorer cet email.\n\n"
            . "© " . date('Y') . " URLink. Tous droits réservés.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("L'email n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}");
        return false;
    }
}

function generate_reset_token() {
    return bin2hex(random_bytes(32));
}

function send_reset_email($to_email, $username, $reset_token) {
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
        $mail->Subject = 'Réinitialisation de mot de passe pour votre compte URLink';

        $reset_link = 'https://' . $_SERVER['HTTP_HOST'] . '/reset_password.php?token=' . $reset_token;

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>URLink Réinitialisation</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333333;
                    margin: 0;
                    padding: 0;
                    background-color: #f9f9f9;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 20px;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                }
                .header { 
                    background: linear-gradient(135deg, #4361ee, #3742fa);
                    color: white; 
                    padding: 20px; 
                    text-align: center;
                    border-radius: 8px 8px 0 0;
                }
                .header h2 {
                    margin: 0;
                    padding: 0;
                    font-size: 24px;
                }
                .logo {
                    font-size: 28px;
                    margin-bottom: 10px;
                }
                .content { 
                    padding: 30px; 
                    background-color: #ffffff;
                }
                .button-container {
                    text-align: center;
                    margin: 30px 0;
                }
                .button { 
                    display: inline-block; 
                    padding: 12px 24px; 
                    background: linear-gradient(135deg, #4361ee, #3742fa);
                    color: white; 
                    text-decoration: none; 
                    border-radius: 4px;
                    font-weight: bold;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                    transition: all 0.3s ease;
                }
                .button:hover {
                    background: linear-gradient(135deg, #3742fa, #324cdd);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                }
                .link-container {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: #f8f9fa;
                    border-radius: 4px;
                    word-break: break-all;
                }
                .footer { 
                    margin-top: 30px;
                    text-align: center;
                    font-size: 12px; 
                    color: #777;
                    padding: 20px;
                    border-top: 1px solid #eeeeee;
                }
                p {
                    margin: 15px 0;
                }
                .notice {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 4px;
                    font-size: 14px;
                    border-left: 4px solid #4361ee;
                    margin-top: 20px;
                }
                .expiry {
                    color: #e74c3c;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">URLink</div>
                    <h2>Réinitialisation de mot de passe</h2>
                </div>
                <div class="content">
                    <p>Bonjour ' . htmlspecialchars($username) . ',</p>
                    
                    <p>Nous avons reçu une demande de réinitialisation de votre mot de passe pour votre compte URLink. Si vous n\'avez pas fait cette demande, vous pouvez ignorer cet email ou contacter notre équipe de support.</p>
                    
                    <p>Pour réinitialiser votre mot de passe, veuillez cliquer sur le bouton ci-dessous :</p>
                    
                    <div class="button-container">
                        <a href="' . $reset_link . '" class="button">Réinitialiser mon mot de passe</a>
                    </div>
                    
                    <p>Ou copiez et collez le lien suivant dans votre navigateur :</p>
                    
                    <div class="link-container">
                        ' . $reset_link . '
                    </div>
                    
                    <p class="expiry">Ce lien expirera dans 1 heure pour des raisons de sécurité.</p>
                    
                    <div class="notice">
                        <strong>Note de sécurité :</strong> URLink ne vous demandera jamais votre mot de passe par email. Si vous avez des doutes sur l\'authenticité de ce message, veuillez accéder directement à notre site web.
                    </div>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' URLink. Tous droits réservés.</p>
                    <p>Ceci est un message automatique, veuillez ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = "Bonjour $username,\n\n"
            . "Nous avons reçu une demande de réinitialisation de votre mot de passe. Si vous n'avez pas fait cette demande, vous pouvez ignorer cet email.\n\n"
            . "Pour réinitialiser votre mot de passe, veuillez visiter ce lien :\n\n"
            . "$reset_link\n\n"
            . "Ce lien expirera dans 1 heure pour des raisons de sécurité.\n\n"
            . "© " . date('Y') . " URLink. Tous droits réservés.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("L'email de réinitialisation n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}");
        return false;
    }
}