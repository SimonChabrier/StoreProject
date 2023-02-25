<?php

namespace App\Tests;

use Faker\Factory as Faker;
use App\Service\MailingService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
// assert for mailer


// penser à passer public: true  à public: false dans services.yaml quand les test sont faits

class MailSendTest extends  KernelTestCase
{   

    // make a mail adress provider
    public function emailAdressesProvider()
    {   
        $adresses = [];

        $faker = Faker::create('fr_FR');
        for ($i=0; $i < 100; $i++) { 
            $adresse = $faker->email();
            array_push($adresses, $adresse);
        }
        return $adresses;
    }
    
    // Message type
    public function email()
    {   
        //$mailer = $this->createMock(MailerInterface::class);
        

        $notificationEmail = (new Email())
                ->from('me@example.com')
                ->to('notification@example.com')
                ->subject('Notification d\'envoi d\'email')
                ->text('L\'email a été envoyé avec succès.');
        //$mailer->send($notificationEmail);
        return $notificationEmail;
    }

    public function testSendUniqueMail(): void
    {
        self::bootKernel();
        $serviceContainer = static::getContainer();

        $mailService = $serviceContainer->get(MailingService::class);
        $mailService->sendEmail('test@test.fr', 'test', 'test');

        // TEST SYNCHRONE
        // désactiver le transport de mail asynchrone dans messenger.yaml pour les tests qui sont en mode synchrone
        // $this->assertEmailCount(2);
        
        // TEST ASYNCHRONE
        // is in the queue
        $this->assertEmailIsQueued($this->getMailerEvent());
        // 0 mail envoyé directement parce que le transport est en mode asynchrone
        $this->assertEmailCount(0);
        // il y a 2 mails dans la file d'attente de messenger 1 pour le destinataire et 1 pour la notification
        $this->assertQueuedEmailCount(2);

        $email = $this->email();
        // test subject
        $this->assertEmailHeaderSame($email, 'Subject', 'Notification d\'envoi d\'email');
        $this->assertEmailTextBodyContains($email, 'L\'email a été envoyé avec succès.');
        // test sender
        $this->assertEmailHeaderSame($email, 'From', 'me@example.com');
        // test recipient
        $this->assertEmailHeaderSame($email, 'To', 'notification@example.com');


    }

    public function testSendMultipleMail(): void
    {
        self::bootKernel();
        $serviceContainer = static::getContainer();

        $mailService = $serviceContainer->get(MailingService::class);
        $adresses = $this->emailAdressesProvider();
        $mailService->sendEmailToRecipients($adresses, 'test', 'test');
        // assert envoie de mail à 3 destinataires + 1 pour la notification
        $this->assertEmailIsQueued($this->getMailerEvent());
        $this->assertEmailCount(0);
        $this->assertQueuedEmailCount(101);
    }
}
