<?php

namespace App\Tests;

use Faker\Factory as Faker;
use App\Service\Notify\EmailService;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MailSendTest extends KernelTestCase
{
    /**
     * @return array
     */
    public function emailAddressesProvider(): array
    {
        $addresses = [];
        $faker = Faker::create('fr_FR');

        for ($i = 0; $i < 20; $i++) {
            $address = $faker->email();
            $addresses[] = [$address];
        }

        return $addresses;
    }

    /**
     * Teste le service EmailService avec un mock de MessageBusInterface
     * pour envoyer une notification admin avec un email
     * @dataProvider emailAddressesProvider
     * @param string $email
     */
    public function testMailIsSent(string $email): void
    {
        $subject = 'test';
        $status = 'test';

        // je crée un mock de MessageBusInterface 
        $bus = $this->createMock(MessageBusInterface::class);
        // je crée un objet Envelope 
        $envelope = new \Symfony\Component\Messenger\Envelope(new \stdClass());

        // l'attendu est que la méthode dispatch soit appelée une fois avec le bon argument
        $bus->expects($this->once())
            ->method('dispatch')
            ->with(new \App\Message\AdminNotification($subject, $email, $status))
            ->willReturn($envelope);

        // Créer une instance réelle de EmailService avec le mock de MessageBusInterface
        $emailService = new EmailService($bus);

        // Appeler la méthode pour envoyer la notification admin
        $emailService->sendAdminNotification($subject, $email, $status);
        
        // Si la méthode dispatch n'est pas appelée, le test échoue
        
    }
}
