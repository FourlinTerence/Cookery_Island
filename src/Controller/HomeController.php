<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de la page d'accueil de Cookery Island.
 * Gère l'affichage principal, la mise en avant du chef et des dernières recettes publiées.
 */
final class HomeController extends AbstractController
{
    /**
     * Point d'entrée principal : charge les 6 recettes les plus récentes
     * ainsi que la liste des catégories pour la navigation et l'affichage.
     */
    #[Route('/', name: 'app_home')]
    public function index(RecipeRepository $recipeRepository, CategoryRepository $categoryRepository): Response
    {
        // Récupération des 6 dernières recettes triées par date de création décroissante
        $recipes = $recipeRepository->findBy([], ['createdAt' => 'DESC'], 6);

        // Récupération de l'ensemble des catégories culinaires
        $categories = $categoryRepository->findAll();

        // Rendu de la vue Twig d'accueil avec injection des variables
        return $this->render('home/index.html.twig', [
            'recipes' => $recipes,
            'categories' => $categories,
        ]);
    }
}
