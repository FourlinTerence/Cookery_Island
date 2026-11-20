<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels pour l'authentification (Connexion & Inscription).
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Vérifie l'accès à la page de connexion.
     */
    public function testLoginPageIsSuccessful(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.banner-title', 'Connexion');
        $this->assertSelectorExists('form.login-form');
    }

    /**
     * Vérifie l'accès à la page d'inscription.
     */
    public function testRegisterPageIsSuccessful(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.banner-title', 'Inscription');
        $this->assertSelectorExists('form.login-form');
    }
}
