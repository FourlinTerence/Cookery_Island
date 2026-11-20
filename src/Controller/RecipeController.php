<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * RecipeController — Contrôleur gérant l'exploration et le détail des recettes.
 *
 * Responsabilités :
 * - Catalogue général des recettes avec pagination (9 par page) et filtres avancés :
 *   temps de préparation+cuisson (rapide/long), difficulté, likes (si connecté), catégorie.
 * - Fiche détaillée d'une recette avec ingrédients, étapes et commentaires.
 */
#[Route(name: 'app_recipe_')]
final class RecipeController extends AbstractController
{
    /**
     * Nombre maximal de recettes affichées par page dans le catalogue.
     */
    private const int RECIPES_PER_PAGE = 9;

    /**
     * Page catalogue présentant l'ensemble des recettes culinaires avec pagination et filtres.
     *
     * Filtres supportés via les paramètres de requête GET :
     * - `search` : recherche textuelle sur le titre ou la description
     * - `category` : filtrage par catégorie culinaire (slug ou identifiant)
     * - `duration` : filtrage par temps total (addition de prepTime + cookTime) :
     *     'fast' => ≤ 40 min
     *     'long' => > 40 min
     * - `difficulty` : filtrage par niveau de difficulté ('Très facile', 'Facile', 'Moyen', 'Difficile')
     * - `liked` : 1 ou true pour afficher uniquement les recettes favorites de l'utilisateur connecté
     * - `sort` : ordre de tri ('newest', 'oldest', 'prep_asc', 'prep_desc', 'title_asc')
     * - `page` : numéro de page pour la pagination (défaut : 1)
     *
     * @param Request            $request            Requête HTTP entrante
     * @param RecipeRepository   $recipeRepository   Repository pour la sélection filtrée et paginée
     * @param CategoryRepository $categoryRepository Repository pour alimenter le sélecteur de catégories
     */
    #[Route('/recettes', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        RecipeRepository $recipeRepository,
        CategoryRepository $categoryRepository
    ): Response {
        // --- 1. Récupération et normalisation du numéro de page ---
        $page = max(1, $request->query->getInt('page', 1));

        // --- 2. Extraction des filtres depuis la requête GET ---
        $filters = [
            'search'     => trim((string)$request->query->get('search', '')),
            'category'   => $request->query->get('category'),
            'duration'   => $request->query->get('duration'),
            'difficulty' => $request->query->get('difficulty'),
            'liked'      => $request->query->getBoolean('liked'),
            'sort'       => (string)$request->query->get('sort', 'newest'),
        ];

        // Utilisateur connecté (si présent) pour le filtre par favoris / likes
        $user = $this->getUser();
        $currentUser = $user instanceof User ? $user : null;

        // --- 3. Exécution de la requête paginée via le Repository ---
        $paginator = $recipeRepository->findPaginatedRecipes(
            filters: $filters,
            page: $page,
            limit: self::RECIPES_PER_PAGE,
            currentUser: $currentUser
        );

        $totalRecipes = count($paginator);
        $totalPages   = max(1, (int)ceil($totalRecipes / self::RECIPES_PER_PAGE));

        // Si la page demandée dépasse le total existant (et qu'il y a des résultats), on borne
        if ($page > $totalPages && $totalRecipes > 0) {
            $page = $totalPages;
        }

        // --- 4. Données auxiliaires pour alimenter les menus déroulants de filtrage ---
        $categories = $categoryRepository->findBy([], ['name' => 'ASC']);
        $difficulties = ['Très facile', 'Facile', 'Moyen', 'Difficile'];

        return $this->render('recipe/index.html.twig', [
            'recipes'      => $paginator,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
            'totalRecipes' => $totalRecipes,
            'limit'        => self::RECIPES_PER_PAGE,
            'filters'      => $filters,
            'categories'   => $categories,
            'difficulties' => $difficulties,
        ]);
    }

    /**
     * Page de détail d'une recette, identifiée par son ID en base de données.
     *
     * @param int              $id               Identifiant de la recette en base
     * @param RecipeRepository $recipeRepository Repository Doctrine injecté par autowiring
     */
    #[Route('/recette/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, RecipeRepository $recipeRepository): Response
    {
        // Chargement de la recette avec ses dépendances
        $recipe = $recipeRepository->find($id);

        if (!$recipe instanceof Recipe) {
            throw new NotFoundHttpException(sprintf('La recette #%d est introuvable.', $id));
        }

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}
