<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * RecipeController — Contrôleur pour l'affichage des recettes culinaires.
 *
 * Gère la page de détail d'une recette : chargement depuis la base de données
 * via Doctrine, et rendu du template Twig avec toutes les informations associées
 * (ingrédients, commentaires, auteur, catégorie…).
 */
#[Route('/recette', name: 'app_recipe_')]
final class RecipeController extends AbstractController
{
    /**
     * Page de détail d'une recette, identifiée par son ID en base de données.
     *
     * L'entité Recipe est chargée via le RecipeRepository Doctrine.
     * Si l'ID n'existe pas, une page 404 est retournée.
     *
     * @param int              $id         Identifiant de la recette en base
     * @param RecipeRepository $repository Repository Doctrine injecté par autowiring
     */
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id, RecipeRepository $repository): Response
    {
        // --- Chargement de la recette depuis la base de données ---
        // On charge également les relations (category, author, recipeIngredients,
        // comments) via le repository pour éviter les requêtes N+1.
        $recipe = $repository->find($id);

        // Si la recette n'existe pas, on lève une 404
        if (!$recipe instanceof Recipe) {
            throw new NotFoundHttpException(sprintf('Recette #%d introuvable.', $id));
        }

        return $this->render('recipe/show.html.twig', [
            // On passe l'entité Recipe directement au template.
            // Twig accèdera aux données via les getters : recipe.title, recipe.author.username, etc.
            'recipe' => $recipe,
        ]);
    }
}
