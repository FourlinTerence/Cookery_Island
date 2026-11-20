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
     * Vérifie que la fiche détaillée d'une recette existante répond en 200,
     * affiche les commentaires dynamiques et invite l'utilisateur anonyme à se connecter.
     */
    public function testRecipeDetailPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/recette/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.recipe-hero');
        // Les commentaires dynamiques doivent être présents
        $this->assertSelectorExists('.recipe-comments-box');
        $this->assertGreaterThan(0, $crawler->filter('.comment-item')->count());
        // L'utilisateur non connecté doit voir l'encart d'invitation à la connexion
        $this->assertSelectorExists('.comment-login-required');
        // Aucun champ pseudo ne doit exister
        $this->assertSelectorNotExists('input[name="pseudo"]');
    }

    /**
     * Vérifie qu'un utilisateur connecté peut publier un commentaire dynamique sans saisir de pseudo.
     */
    public function testAuthenticatedUserCanPostComment(): void
    {
        $client = static::createClient();

        // Récupération de l'utilisateur de test en base
        $userRepository = static::getContainer()->get(\App\Repository\UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'user1@test.com']);
        $this->assertNotNull($testUser);

        // Connexion de l'utilisateur
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/recette/1');
        $this->assertResponseIsSuccessful();

        // Vérifie la présence du formulaire et du badge utilisateur
        $this->assertSelectorExists('.comment-user-badge');
        $this->assertSelectorExists('form.comment-form');
        // Pas de champ pseudo
        $this->assertSelectorNotExists('input[name="pseudo"]');

        // Soumission du formulaire de commentaire
        $form = $crawler->filter('form.comment-form')->form([
            'comment[content]' => 'Un délice réunionnais absolu testé ce soir ! Merci pour la recette.',
        ]);

        $client->submit($form);

        // Redirection vers la recette après publication
        $this->assertResponseRedirects('/recette/1#commentaires');
        $client->followRedirect();

        // Message de succès affiché
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Votre commentaire a été publié avec succès');
        // Le commentaire soumis doit maintenant apparaître dans la page
        $this->assertSelectorTextContains('.comments-list', 'Un délice réunionnais absolu testé ce soir !');
    }
}
