<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebTestCaseTest extends WebTestCase
{   
    public function testHome(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        // attendu : reponse 200
        $this->assertResponseIsSuccessful();
    }

    public function testAdminAccess(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin');
        // attendu : redirection vers la page de login
        $this->assertResponseRedirects('/login');
    }

    public function testLogin(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        
        // attendu : reponse 200
        $this->assertResponseIsSuccessful();
        
        $form = $crawler->selectButton('LogIn')->form();
        $form['email'] = 'admin@admin.fr';
        $form['password'] = 'password';
        $client->submit($form);

        // Attendu : redirection vers la page d'accueil
        $this->assertResponseRedirects('/');

        // Récupérer le service security.token_storage
        $tokenStorage = self::$container->get('security.token_storage');
        
        // attendu : que le token d'authentification ne soit pas null
        $this->assertNotNull($tokenStorage->getToken());
        // Attendu : que le token d'authentification soit valide et que l'utilisateur soit bien connecté
        $this->assertTrue($tokenStorage->getToken()->getUser() instanceof User);

        // Récupérer l'utilisateur sur la session en cours 
        $user = $tokenStorage->getToken()->getUser();
        // attendu : que l'utilisateur soit bien une instance de User
        $this->assertInstanceOf(User::class, $user);
        // attendu : que l'utilisateur soit bien l'admin
        $this->assertEquals('admin@admin.fr', $user->getUsername());
    }

    public function testLogout(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/logout');
        // attendu redirection vers la page d'accueil 
        $this->assertResponseRedirects('http://localhost/');
        // Récupérer le service security.token_storage
        $tokenStorage = self::$container->get('security.token_storage');
        // attendu : que le token d'authentification soit null et que la déconnexion soit effective
        $this->assertNull($tokenStorage->getToken());
    }
}
