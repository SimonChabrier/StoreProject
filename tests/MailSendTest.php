<?php

namespace App\Tests;

use Faker\Factory as Faker;
use App\Service\Notify\EmailService;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Repository\UserRepository;


class TestMessage
{
    private $subject;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }
}

class MailSendTest extends KernelTestCase
{   

    protected function setUp(): void
    {
        self::bootKernel();
        // je récupère le UserRepository depuis le conteneur de services
        $this->userRepository = self::$container->get(UserRepository::class);
    }

    /**
     * Teste l'envoi d'un mail à chaque adresse email de la liste
     */
    public function testBatchMailSending(): void
    {
        $faker = Faker::create('fr_FR');

        $subject = $faker->sentence();
        $status = $faker->sentence();

        // Récupérer la liste des utilisateurs depuis le UserRepository
        $users = $this->userRepository->findAll();
        // Je crée un mock du MessageBusInterface
        $bus = $this->createMock(MessageBusInterface::class);
        // L'attendu est que dispatch soit appelé exactement avec le nombre d'utilisateurs dans la liste
        $bus->expects($this->exactly(count($users)))
            ->method('dispatch')
            ->with($this->anything())
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new TestMessage($subject)));
        // Je crée une instance de EmailService en lui passant le mock du MessageBusInterface
        $emailService = new EmailService($bus);
        // je prépare un tableau pour stocker les résultats
        $results = [];
        // Pour chaque utilisateur dans la liste, j'appelle la méthode sendAdminNotification de EmailService
        foreach ($users as $user) {
            $results[] = $emailService->sendAdminNotification($subject, $user->getEmail(), $status);
        }
        // Je vérifie que chaque résultat est une instance de Envelope (la classe de base des messages envoyés par Messenger)
        $this->assertContainsOnlyInstancesOf(\Symfony\Component\Messenger\Envelope::class, $results);
        // Je vérifie que chaque message envoyé contient bien le sujet du mail
        foreach ($results as $result) {
            $this->assertEquals($subject, $result->getMessage()->getSubject());
        }
        // je vérifie que le nombre de résultats est bien égal au nombre d'utilisateurs dans la liste
        $this->assertEquals(count($users), count($results));
    }

   
}

