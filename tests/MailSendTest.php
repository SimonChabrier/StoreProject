<?php

namespace App\Tests;

use App\Entity\Product;
use Faker\Factory as Faker;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Service\File\UploadService;
use App\Service\File\ResizerService;
use App\Service\Notify\EmailService;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


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
        $this->userRepository = self::$container->get(UserRepository::class);
        $this->adminEmail = self::$container->getParameter('admin_email');
    }

    /**
     * Teste l'envoi d'un mail à chaque adresse email de la liste
     * pour tester l'envoie de type newsletter
     * attendu : que la méthode sendTemplateEmailNotification de EmailService
     * soit appelée exactement une fois pour chaque utilisateur disposant d'un compte
     */
    public function testBatchMailSending(): void
    {   
        $subject = 'test_subject';
        $users = $this->userRepository->findAll();
        $usersCount = count($users);
        echo "nombre d'utilisateurs dans la liste : " . $usersCount . "\n";

        $bus = $this->createMock(MessageBusInterface::class);
        // L'attendu est que dispatch soit appelé exactement avec le nombre d'utilisateurs dans la liste
        $bus->expects($this->exactly($usersCount))
            ->method('dispatch')
            ->with($this->anything())
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new TestMessage($subject)));
        echo "nombre d'appels à la méthode dispatch : " . $usersCount . "\n";
        // Je crée une instance de EmailService en lui passant le mock du MessageBusInterface
        $emailService = new EmailService($bus);
        // je prépare un tableau pour stocker les résultats
        $results = [];
        // Pour chaque utilisateur dans la liste, j'appelle la méthode  sendTemplateEmailNotification($from, $to, $subject, $template, $context) de EmailService
        foreach ($users as $user) {
            $results[] = $emailService->sendTemplateEmailNotification(
                'from@example.com',
                $user->getEmail(),
                $subject,
                'template_name',
                ['key' => 'value'] // Context
            );
        }
        $resultsCount = count($results);
        // attendu que le nombre de résultats soit égal au nombre d'utilisateurs dans la liste
        $this->assertEquals($usersCount, $resultsCount);
        echo "nombre de mail envoyés : " . $resultsCount . "\n";
        // Jattendu que chaque résultat est une instance de Envelope (la classe de base des messages envoyés par Messenger)
        $this->assertContainsOnlyInstancesOf(\Symfony\Component\Messenger\Envelope::class, $results);
        // attendu que le sujet de chaque message envoyé soit bien le même que celui passé en paramètre
        foreach ($results as $result) {
            $this->assertEquals($subject, $result->getMessage()->getSubject());
        }
    }

    // Teste l'envoi d'un mail à l'admin du site
    // attendu : que la méthode sendAdminNotification($subject, $email, $status) de EmailService soit appelée exactement une fois
    // que le mail de l'admin soit bien récupéré depuis le .env.test depuis service.yaml
    public function testAdminMailSending(): void
    {   
        $subject = 'test_subject';
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->anything())
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new TestMessage($subject)));
        $emailService = new EmailService($bus);
        $results = [];
        $results[] = $emailService->sendAdminNotification(
            $subject,
            $this->adminEmail,
            'status'
        );
        // attendu obtenir uniquement des instances de Envelope (la classe de base des messages envoyés par Messenger)
        $this->assertContainsOnlyInstancesOf(\Symfony\Component\Messenger\Envelope::class, $results);
        foreach ($results as $result) {
            // attendu que chaque message envoyé contienne bien le sujet du mail 
            $this->assertEquals($subject, $result->getMessage()->getSubject());
        }
        // attendu que la méthode sendAdminNotification($subject, $email, $status) de EmailService soit appelée exactement une fois
        $this->assertEquals(1, count($results));
    }

   
}

