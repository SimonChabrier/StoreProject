<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebTestCaseTest extends WebTestCase
{
    public function testHome(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

    }

    public function testAdmin(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin');
        $this->assertResponseRedirects('/login');
    }

    public function testLogin(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('LogIn')->form();
        $form['email'] = 'admin@admin.fr';
        $form['password'] = 'password';
        $client->submit($form);
        // attendu : redirection vers la page d'accueil
        $this->assertResponseRedirects('/');
    }
}
