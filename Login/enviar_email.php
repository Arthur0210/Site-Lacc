<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inclui os arquivos do PHPMailer
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';

// Inclui o arquivo de configuração para usar as constantes de SMTP
require_once __DIR__ . '/config.php';

function enviarEmail($destinatario, $assunto, $linkRedefinicao) {
    $mail = new PHPMailer(true);

    try {
        // Configuração do servidor SMTP usando constantes
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Remetente e destinatário
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($destinatario);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = "
            <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <h2 style='color: #1F4F85;'>Redefinição de Senha - LACC</h2>
                    <p>Olá,</p>
                    <p>Você solicitou a redefinição da sua senha.</p>
                    <p>Clique no link abaixo para criar uma nova senha:</p>
                    <p><a href='{$linkRedefinicao}' style='background: #1F4F85; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;'>Redefinir Senha</a></p>
                    <p>Este link é válido por 1 hora.</p>
                    <p>Se você não solicitou isso, por favor, ignore este e-mail.</p>
                    <p>Atenciosamente,<br><strong>Equipe LACC</strong></p>
                </body>
            </html>
        ";
        $mail->AltBody = "Para redefinir sua senha, acesse o seguinte link: {$linkRedefinicao}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Em produção, logar o erro em um arquivo em vez de exibi-lo
        error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
        return false;
    }
}