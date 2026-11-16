<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Content;
use App\Models\EmailRelay;

require_once app_path('Libraries/PHPMailer/PHPMailer.php');
require_once app_path('Libraries/PHPMailer/SMTP.php');
require_once app_path('Libraries/PHPMailer/Exception.php');

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

class SmtpBlastService
{
    /**
     * Mengirim 1 email menggunakan kredensial relay yang diambil langsung dari database server
     * (password tidak pernah dikirim ke browser).
     */
    public function sendSingleEmail(
        Brand $brand,
        Content $content,
        EmailRelay $relay,
        string $recipientEmail,
        string $subject,
        bool $simulateOnly = false
    ): array {
        $hostName = $relay->emailHost ? $relay->emailHost->host : '';
        $fromAddress = $relay->email_from ?: $relay->email;
        $fromAlias = $brand->email_alias ?: ($relay->email_alias ?: $brand->name);

        // Substitusi tag dinamis {email} dengan alamat email penerima
        $personalizedBody = str_replace('{email}', $recipientEmail, (string) $content->body_content);

        if ($simulateOnly) {
            return [
                'success'    => true,
                'simulated'  => true,
                'message'    => 'Simulated Success (Sandbox Mode)',
                'relay_used' => $relay->email . ' (' . $hostName . ')',
                'from_used'  => $fromAddress . ' - ' . $fromAlias,
                'sent_at'    => now()->format('Y-m-d H:i:s'),
            ];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $hostName;
            $mail->SMTPAuth   = true;
            $mail->Username   = $relay->email;
            $mail->Password   = $relay->password;
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = (int) ($relay->port ?: 465);
            $mail->Timeout    = 20;
            $mail->setFrom($fromAddress, $fromAlias);
            $mail->addAddress($recipientEmail, '');
            $mail->CharSet    = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject    = $subject;
            $mail->Body       = $personalizedBody;
            $mail->AltBody    = 'Sorry, cannot show this page. Your email client does not support HTML format.';
            $mail->send();

            return [
                'success'    => true,
                'simulated'  => false,
                'message'    => 'Success',
                'relay_used' => $relay->email . ' (' . $hostName . ')',
                'from_used'  => $fromAddress . ' - ' . $fromAlias,
                'sent_at'    => now()->format('Y-m-d H:i:s'),
            ];
        } catch (PHPMailerException $e) {
            return [
                'success'    => false,
                'simulated'  => false,
                'message'    => strip_tags($e->errorMessage()),
                'relay_used' => $relay->email . ' (' . $hostName . ')',
                'from_used'  => $fromAddress . ' - ' . $fromAlias,
                'sent_at'    => now()->format('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $e) {
            return [
                'success'    => false,
                'simulated'  => false,
                'message'    => $e->getMessage(),
                'relay_used' => $relay->email . ' (' . $hostName . ')',
                'from_used'  => $fromAddress . ' - ' . $fromAlias,
                'sent_at'    => now()->format('Y-m-d H:i:s'),
            ];
        }
    }
}

