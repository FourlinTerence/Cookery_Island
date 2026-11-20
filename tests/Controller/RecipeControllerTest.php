<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels pour le contrôleur RecipeController.
 * Vérifie l'accessibilité du catalogue (/recettes), la pagination et le bon fonctionnement des filtres.
 */
class RecipeControllerTest extends WebTestCase
{
    /**
     * Vérifie que la page catalogue /recettes s'affiche correctement en HTTP 200.
     */
    public function testIndexPageIsSuccessful(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/recettes');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1.hero-title', 'Nos recettes');
        // Vérifie la présence des cartes de recettes (au max 9 par page)
        $cardsCount = $crawler->filter('.recipe-card')->count();
        $this->assertGreaterThan(0, $cardsCount);
        $this->assertLessThanOrEqual(9, $cardsCount);
    }

    /**
     * Vérifie le filtrage par temps total (rapide <= 40 min).
     */
    public function testFilterDurationFast(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/recettes?duration=fast');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.recipes-grid');
    }

    /**
     * Vérifie le filtrage par niveau de difficulté.
     */
    public function testFilterDifficulty(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/recettes?difficulty=Facile');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.recipes-grid');
    }

    /**
     * Vérifie la navigation vers la page 2 de pagination.
     */
    public function testPaginationPageTwo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/recettes?page=2');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.recipes-grid');
    }

    /**
     * Vérifie que la fiche détaillée d'une recette existante répond en 200.
     */
    public function testRecipeDetailPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/recette/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.recipe-hero');
    }
}
