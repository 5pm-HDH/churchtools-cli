<?php

namespace CTExport\Mail;

use CTExport\ApplicationSettings;
use PHPMailer\PHPMailer\PHPMailer;

class MailBuilder
{

    private string $subject = "ChurchTools CLI Tool Information";
    private string $mailBody = "Attachments from CT-CLI.";
    private array $toAddresses = [];
    private array $attachments = [];

    private function __construct()
    {
    }

    public static function forAttachments(array $fileAttachments, array $toMailAddresses): MailBuilder
    {
        $mailBuilder = new MailBuilder();
        foreach ($fileAttachments as $fileAttachment) {
            $mailBuilder->addAttachment($fileAttachment);
        }
        $mailBuilder->addToAddresses($toMailAddresses);
        return $mailBuilder;
    }


    public function withSubject(string $subject): MailBuilder
    {
        $this->subject = $subject;
        return $this;
    }

    public function addToAddresses(array $toAddresses): MailBuilder
    {
        foreach ($toAddresses as $toAddress) {
            $this->addToAddress($toAddress);
        }
        return $this;
    }

    public function addToAddress(string $toAddress): MailBuilder
    {
        $this->toAddresses[] = $toAddress;
        return $this;
    }

    public function addAttachment(string $filePath): MailBuilder
    {
        $this->attachments[] = $filePath;
        return $this;
    }

    public function send()
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = ApplicationSettings::getSettingForKey(ApplicationSettings::SETTING_MAIL_HOST);
        $mail->SMTPAuth = true;
        $mail->Username = ApplicationSettings::getSettingForKey(ApplicationSettings::SETTING_MAIL_USER);
        $mail->Password = ApplicationSettings::getSettingForKey(ApplicationSettings::SETTING_MAIL_PASSWORD);
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = ApplicationSettings::getSettingForKey(ApplicationSettings::SETTING_MAIL_PORT);

        $mail->setFrom(ApplicationSettings::getSettingForKey(ApplicationSettings::SETTING_MAIL_FROM));
        foreach ($this->toAddresses as $toAddress) {
            if (!empty($toAddress)) {
                $mail->addAddress($toAddress);
            }
        }
        foreach ($this->attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        $mail->isHTML(true);
        $mail->Subject = $this->subject;
        $mail->Body = $this->mailBody;
        $mail->send();

    }
}