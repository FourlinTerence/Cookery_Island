<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant l'affichage des recettes culinaires.
 * Présente le détail d'une recette avec ses ingrédients, ses étapes de préparation
 * et son espace de commentaires conformément aux maquettes de Cookery Island.
 */
final class RecipeController extends AbstractController
{
    /**
     * Page de détail d'une recette.
     * En l'absence temporaire de données en base, affiche les données détaillées
     * de la recette emblématique réunionnaise : Rougail Saucisse Fumé.
     *
     * @param int|null $id Identifiant optionnel de la recette
     */
    #[Route('/recette/{id}', name: 'app_recipe_show', defaults: ['id' => 1])]
    public function show(?int $id = 1): Response
    {
        // Données fictives complètes pour la recette du Rougail Saucisse Fumé
        $recipe = [
            'id' => $id ?? 1,
            'title' => 'Rougail Saucisse Fumé',
            'category' => 'Plat Traditionnel Réunionnais',
            'difficulty' => 'Facile',
            'prepTime' => 20,
            'cookTime' => 35,
            'totalTime' => 55,
            'servings' => 4,
            'image' => 'rougail-saucisse.jpg',
            'secondaryImage' => 'cari-camarons-réunionnais.jpg',
            'description' => "L'incontournable de la gastronomie réunionnaise ! De savoureuses saucisses fumées rissolées, mijotées dans une sauce parfumée aux tomates fraîches, oignons, curcuma, gingembre et piments.",
            'ingredients' => [
                'col1' => [
                    '6 saucisses fumées créoles (ou saucisses de Montbéliard)',
                    '4 belles tomates mûres concassées en petits dés',
                    '3 oignons moyens émincés finement',
                    '4 gousses d\'ail pilées avec une pincée de gros sel',
                ],
                'col2' => [
                    '1 morceau de gingembre frais râpé (environ 2 cm)',
                    '3 à 4 petits piments oiseaux (à doser selon convenance)',
                    '1 cuillère à café rase de curcuma moulu ("safran péi")',
                    '3 branches de thym frais et 2 c. à soupe d\'huile neutre',
                ],
            ],
            'steps' => [
                [
                    'title' => 'Étape 1 : Préparation et roussissage des saucisses',
                    'content' => "Piquez les saucisses et faites-les bouillir dans une grande casserole d'eau frémissante pendant 10 à 15 minutes afin de les dessaler et retirer l'excédent de graisse. Égouttez-les soigneusement puis découpez-les en rondelles d'environ 1,5 à 2 cm d'épaisseur. Dans une sauteuse ou une marmite traditionnelle en fonte, chauffez deux cuillères à soupe d'huile à feu vif et faites dorer les rondelles de saucisses jusqu'à obtenir une belle coloration croustillante.",
                ],
                [
                    'title' => 'Étape 2 : Confection de la sauce créole et mijotage',
                    'content' => "Ajoutez les oignons émincés aux saucisses dorées et laissez-les suer à feu moyen jusqu'à ce qu'ils deviennent fondants et légèrement translucides. Incorporez ensuite le mélange pilé d'ail, gingembre et piment, ainsi que le thym effeuillé et le curcuma. Remuez énergiquement pendant 2 minutes pour libérer tous les parfums. Ajoutez enfin les dés de tomates fraîches, mélangez intimement, couvrez et laissez mijoter à feu doux pendant environ 20 minutes jusqu'à ce que la sauce soit onctueuse et bien rouge. Servez brûlant accompagné d'un bon riz blanc chaud et de grains (lentilles de Cilaos ou haricots rouges).",
                ],
            ],
            'comments' => [
                [
                    'author' => 'Marmite974',
                    'date' => 'Il y a 2 jours',
                    'avatar' => '👨‍🍳',
                    'rating' => 5,
                    'content' => "Oté ! Un véritable classique de chez nous parfaitement expliqué. J'ai rajouté un petit zeste de combava râpé en fin de cuisson, un pur délice comme chez mémé à Saint-Pierre !",
                ],
                [
                    'author' => 'Sophie Gourmande',
                    'date' => 'Il y a 5 jours',
                    'avatar' => '👩‍🍳',
                    'rating' => 5,
                    'content' => "Recette testée et approuvée par toute la famille ce week-end ! Les explications sont limpides et la sauce tomate bien relevée au curcuma et thym frais était à tomber. Merci pour ce partage !",
                ],
            ],
        ];

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}
