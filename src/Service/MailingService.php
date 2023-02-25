<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailingService extends AbstractController
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail($recipient, $subject, $body)
    {
        $email = (new Email())
            ->from('me@example.com')
            ->to($recipient)
            ->subject($subject)
            ->text($body);
        try {
            $sentCount = $this->mailer->send($email);
            $this->sendConfirmationEmail($sentCount, $recipient);
        } catch (TransportExceptionInterface $e) {
            // L'email n'a pas été envoyé
           return false;
        }

        // $this->mailer->send($email);

        // $sentCount = $this->mailer->send($email);
        // $this->sendConfirmationEmail($sentCount, $recipient);
        
    }

    public function sendConfirmationEmail($sentCount, $recipient)
    {
        if ($sentCount > 0) {
            // L'email a été envoyé avec succès
            $notificationEmail = (new Email())
                ->from('me@example.com')
                ->to('notification@example.com')
                ->subject('Notification d\'envoi d\'email')
                ->text('L\'email a été envoyé avec succès.');
            $this->mailer->send($notificationEmail);
            return true;
        } else {
            // L'email n'a pas été envoyé
            $notificationEmail = (new Email())
                ->from('me@example.com')
                ->to('notification@example.com')
                ->subject('Echec d\'envoi d\'email')
                ->text('L\'email n\'a pas été envoyé à ' . $recipient . ' .');
            $this->mailer->send($notificationEmail);
            return false;
        }
    }

    
    public function sendEmailWithTemplate($recipient, $subject, $template, $context)
    {
        $email = (new Email())
            ->from('mail@example.com')
            ->to($recipient)
            ->subject($subject)
            ->html($this->renderView($template, $context));

            $sentCount = $this->mailer->send($email);
            $this->sendConfirmationEmail($sentCount, $recipient);
    }

    public function sendEmailWithAttachment($recipient, $subject, $body, $attachment)
    {
        $email = (new Email())
            ->from('mail@example.com')
            ->to($recipient)
            ->subject($subject)
            ->text($body)
            ->attachFromPath($attachment);

            $sentCount = $this->mailer->send($email);
            $this->sendConfirmationEmail($sentCount, $recipient);
    }

    public function sendEmailWithTemplateAndAttachment($recipient, $subject, $template, $context, $attachment)
    {
        $email = (new Email())
            ->from('mail@exemple.com')
            ->to($recipient)
            ->subject($subject)
            ->html($this->renderView($template, $context))
            ->attachFromPath($attachment);

        $sentCount = $this->mailer->send($email);
        $this->sendConfirmationEmail($sentCount, $recipient);
    }

    // mail to recipents in array with the same subject and body 
    public function sendEmailToRecipients($recipients, $subject, $body)
    {
        $total = count($recipients);
        $i = 0;

        $recipientFailed = [];

            foreach ($recipients as $recipient) {
                $email = (new Email())
                    ->from('mail@exemple.com')
                    ->to($recipient)
                    ->subject($subject)
                    ->text($body);

                    try {
                        $this->mailer->send($email);
                    } catch (TransportExceptionInterface $e) {
                        // feed getFailedRecipients() with each recipient in failed on the loop 
                        $recipientFailed[] = filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false ? $recipient : 'Email invalide pour: ' . $recipient;
                        $recipientFailed = array_filter($recipientFailed, function ($recipient) {
                        return $recipient !== null;
                        });
                    }
                $i++;
            }

            if($total == $i) {
                // ok on a envoyé à tous les destinataires
                $confirmationMessage = 'Email envoyé à ' . $i . ' destinataires sur ' . $total .' au total';
                $i = 0;

                $confirmationEmail = (new Email())
                    ->from('mailsendconfirmation@sneakers-shop.com')
                    ->to('admin@sneakers-shop.com')
                    ->subject('Confirmation d\'envoi de mail')
                    ->text($confirmationMessage);
                    
                    $this->mailer->send($confirmationEmail);

                    return $total;

            } else {

                // il manque des mails à envoyer

                $failedCount = $total - $i;
                $failedMessage = 'Email envoyé à ' . $i . ' destinataires sur ' . $total .' au total. ' . $failedCount . ' mails n\'ont pas été envoyés.';
                $i = 0;

                //$recipentsInError = implode(', ', $recipientFailed);
                $recipentsInError = implode(PHP_EOL, $recipientFailed);

                $failedEmail = (new Email())
                    ->from('error@sneakers-shop.com')
                    ->to('admin@sneakers-shop.com')
                    ->subject('Erreur d\'envoi de mail')
                    ->text($failedMessage . ' Les mails n\'ont pas été envoyés à : ' . $recipentsInError);
                    $this->mailer->send($failedEmail);
                
                    return $failedCount;
        }
        
    }

}