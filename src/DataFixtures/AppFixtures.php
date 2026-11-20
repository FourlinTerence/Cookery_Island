<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Entity\RecipeLike;
use App\Entity\Unit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * AppFixtures — Données de démonstration pour Cookery Island.
 *
 * Insère en base :
 *   - 4 Utilisateurs (3 membres + 1 admin)
 *   - 5 Catégories (Plats réunionnais, Desserts, Entrées, Végétarien, Brunch)
 *   - 10 Unités de mesure courantes
 *   - 46 Ingrédients réunionnais et créoles
 *   - 15 Recettes avec leurs ingrédients liés
 *   - 2 Commentaires par recette (30 au total)
 *
 * Commande : php bin/console doctrine:fixtures:load --no-interaction
 */
class AppFixtures extends Fixture
{
    /**
     * Le service de hachage de mots de passe Symfony.
     * Il est injecté automatiquement par autowiring grâce au type-hint.
     */
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * Point d'entrée principal : charge toutes les données dans l'ordre.
     * L'ordre est important : les entités dépendantes doivent être créées
     * et flushées avant de pouvoir être référencées par d'autres entités.
     */
    public function load(ObjectManager $manager): void
    {
        // --- Étape 1 : Utilisateurs (aucune dépendance) ---
        $users = $this->loadUsers($manager);

        // --- Étape 2 : Catégories (aucune dépendance) ---
        $categories = $this->loadCategories($manager);

        // --- Étape 3 : Unités de mesure (aucune dépendance) ---
        $units = $this->loadUnits($manager);

        // --- Étape 4 : Ingrédients (aucune dépendance) ---
        $ingredients = $this->loadIngredients($manager);

        // Premier flush : persiste les entités de base pour qu'elles aient des IDs
        $manager->flush();

        // --- Étape 5 : Recettes avec leurs ingrédients et commentaires ---
        $this->loadRecipes($manager, $users, $categories, $units, $ingredients);

        // Flush final : persiste recettes, RecipeIngredient et commentaires
        $manager->flush();
    }

    // =========================================================================
    // UTILISATEURS
    // =========================================================================

    /**
     * Crée 4 utilisateurs : 1 admin + 3 membres normaux.
     * Les mots de passe sont hachés via le service Symfony (bcrypt/argon2).
     *
     * @return array<string, User> tableau indexé par clé logique
     */
    private function loadUsers(ObjectManager $manager): array
    {
        // --- Admin : accès complet au back-office ---
        $admin = new User();
        $admin->setEmail('admin1@test.com')
              ->setUsername('Admin Cookery')
              ->setRoles(['ROLE_ADMIN'])
              ->setPassword($this->passwordHasher->hashPassword($admin, 'admin1'))
              ->setIsBanned(false)
              ->setCreatedAt(new \DateTimeImmutable('2026-01-01'));
        $manager->persist($admin);

        // --- Membre 1 : auteure active ---
        $user1 = new User();
        $user1->setEmail('user1@test.com')
              ->setUsername('Marie Payet')
              ->setRoles([])
              ->setPassword($this->passwordHasher->hashPassword($user1, 'user1'))
              ->setIsBanned(false)
              ->setCreatedAt(new \DateTimeImmutable('2026-02-10'));
        $manager->persist($user1);

        // --- Membre 2 : lecteur et commentateur ---
        $user2 = new User();
        $user2->setEmail('user2@test.com')
              ->setUsername('Jean-Pierre V.')
              ->setRoles([])
              ->setPassword($this->passwordHasher->hashPassword($user2, 'user2'))
              ->setIsBanned(false)
              ->setCreatedAt(new \DateTimeImmutable('2026-03-05'));
        $manager->persist($user2);

        // --- Membre 3 : passionnée de cuisine créole ---
        $user3 = new User();
        $user3->setEmail('user3@test.com')
              ->setUsername('Sandrine Grondin')
              ->setRoles([])
              ->setPassword($this->passwordHasher->hashPassword($user3, 'user3'))
              ->setIsBanned(false)
              ->setCreatedAt(new \DateTimeImmutable('2026-04-20'));
        $manager->persist($user3);

        return [
            'admin' => $admin,
            'user1' => $user1,
            'user2' => $user2,
            'user3' => $user3,
        ];
    }

    // =========================================================================
    // CATÉGORIES
    // =========================================================================

    /**
     * Crée 5 catégories pour organiser les recettes.
     * Les icônes pointent vers les fichiers présents dans assets/images/.
     *
     * @return array<string, Category> tableau indexé par clé logique
     */
    private function loadCategories(ObjectManager $manager): array
    {
        $data = [
            'reunionnais' => ['name' => 'Plats Réunionnais',  'slug' => 'plats-reunionnais',  'icon' => 'iconePlat (2)-modified.png'],
            'desserts'    => ['name' => 'Desserts',           'slug' => 'desserts',            'icon' => 'Icone-dessert.png'],
            'entrees'     => ['name' => 'Entrées',            'slug' => 'entrees',             'icon' => 'icone_entree1.png'],
            'vegetarien'  => ['name' => 'Végétarien',         'slug' => 'vegetarien',          'icon' => null],
            'brunch'      => ['name' => 'Brunch & Petit-déj', 'slug' => 'brunch-petit-dej',    'icon' => null],
        ];

        $categories = [];
        foreach ($data as $key => $item) {
            $cat = new Category();
            $cat->setName($item['name'])
                ->setSlug($item['slug'])
                ->setIcon($item['icon']);
            $manager->persist($cat);
            $categories[$key] = $cat;
        }

        return $categories;
    }

    // =========================================================================
    // UNITÉS DE MESURE
    // =========================================================================

    /**
     * Crée les unités de mesure courantes utilisées en cuisine créole et française.
     *
     * @return array<string, Unit> tableau indexé par clé logique
     */
    private function loadUnits(ObjectManager $manager): array
    {
        $data = [
            'g'      => ['name' => 'Gramme',         'abbr' => 'g'],
            'kg'     => ['name' => 'Kilogramme',      'abbr' => 'kg'],
            'ml'     => ['name' => 'Millilitre',      'abbr' => 'ml'],
            'cl'     => ['name' => 'Centilitre',      'abbr' => 'cl'],
            'l'      => ['name' => 'Litre',           'abbr' => 'l'],
            'cs'     => ['name' => 'Cuillère à soupe','abbr' => 'c. à s.'],
            'cc'     => ['name' => 'Cuillère à café', 'abbr' => 'c. à c.'],
            'pce'    => ['name' => 'Pièce',           'abbr' => 'pce'],
            'pincee' => ['name' => 'Pincée',          'abbr' => 'pincée'],
            'botte'  => ['name' => 'Botte',           'abbr' => 'botte'],
        ];

        $units = [];
        foreach ($data as $key => $item) {
            $unit = new Unit();
            $unit->setName($item['name'])
                 ->setAbbreviation($item['abbr']);
            $manager->persist($unit);
            $units[$key] = $unit;
        }

        return $units;
    }

    // =========================================================================
    // INGRÉDIENTS
    // =========================================================================

    /**
     * Crée tous les ingrédients nécessaires aux 15 recettes.
     * Les slugs sont générés automatiquement en translittérant l'UTF-8 vers l'ASCII.
     *
     * @return array<string, Ingredient> tableau indexé par clé logique
     */
    private function loadIngredients(ObjectManager $manager): array
    {
        $names = [
            'saucisse_fumee' => 'Saucisse fumée',
            'tomate'         => 'Tomate',
            'oignon'         => 'Oignon',
            'ail'            => 'Ail',
            'gingembre'      => 'Gingembre',
            'curcuma'        => 'Curcuma',
            'huile'          => 'Huile végétale',
            'sel'            => 'Sel',
            'poivre'         => 'Poivre',
            'piment'         => 'Piment',
            'riz'            => 'Riz',
            'crevette'       => 'Crevette',
            'poulet'         => 'Poulet',
            'boeuf'          => 'Bœuf',
            'porc'           => 'Porc',
            'carotte'        => 'Carotte',
            'haricot_vert'   => 'Haricot vert',
            'chou'           => 'Chou',
            'salade'         => 'Salade verte',
            'lentille'       => 'Lentille',
            'pois_du_cap'    => 'Pois du Cap',
            'thon'           => 'Thon',
            'morue'          => 'Morue',
            'citron'         => 'Citron',
            'beurre'         => 'Beurre',
            'farine'         => 'Farine',
            'sucre'          => 'Sucre',
            'oeuf'           => 'Œuf',
            'lait'           => 'Lait',
            'chocolat'       => 'Chocolat noir',
            'vanille'        => 'Extrait de vanille',
            'rhum'           => 'Rhum',
            'banane'         => 'Banane',
            'ananas'         => 'Ananas',
            'coco'           => 'Lait de coco',
            'coriandre'      => 'Coriandre fraîche',
            'thym'           => 'Thym',
            'laurier'        => 'Laurier',
            'safran'         => 'Safran pays (curcuma local)',
            'massale'        => 'Massalé (épices)',
            'combava'        => 'Combava (zeste)',
            'creme'          => 'Crème fraîche',
            'fromage'        => 'Fromage râpé',
            'pain'           => 'Pain de mie',
            'olive'          => 'Olives noires',
        ];

        $ingredients = [];
        foreach ($names as $key => $name) {
            // Génère un slug ASCII depuis le nom (translittération UTF-8)
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
            $slug  = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $ascii), '-'));

            $ingredient = new Ingredient();
            $ingredient->setName($name)->setSlug($slug);
            $manager->persist($ingredient);
            $ingredients[$key] = $ingredient;
        }

        return $ingredients;
    }

    // =========================================================================
    // RECETTES
    // =========================================================================

    /**
     * Crée 15 recettes créoles et réunionnaises avec leurs ingrédients et commentaires.
     * Les noms de fichiers d'images correspondent aux fichiers présents dans assets/images/.
     *
     * @param array<string, User>       $users
     * @param array<string, Category>   $categories
     * @param array<string, Unit>       $units
     * @param array<string, Ingredient> $ingredients
     */
    private function loadRecipes(
        ObjectManager $manager,
        array $users,
        array $categories,
        array $units,
        array $ingredients
    ): void {
        /*
         * Catalogue des 15 recettes.
         * Chaque entrée contient :
         *  - title, slug, description, instructions (texte multi-étapes)
         *  - imageName, imageTwoName → fichiers dans assets/images/
         *  - prepTime (min), cookTime (min), servings, difficulty
         *  - author (clé dans $users), category (clé dans $categories)
         *  - ingredientsList : [[clé_ingredient, quantité, clé_unité], ...]
         *  - comments : [[clé_auteur, contenu, 'YYYY-MM-DD'], ...]
         */
        $recipesData = [

            // ----------------------------------------------------------------
            // 1. Rougail Saucisse Fumé
            // ----------------------------------------------------------------
            [
                'title'       => 'Rougail Saucisse Fumé',
                'slug'        => 'rougail-saucisse-fume',
                'description' => 'Le plat emblématique de La Réunion. Des saucisses fumées mijotées dans une sauce tomate épicée parfumée au gingembre et au curcuma.',
                'instructions'=> "Faites blanchir les saucisses 10 min dans de l'eau bouillante, égouttez et découpez en rondelles.\n\nFaites revenir l'oignon et l'ail émincés dans l'huile chaude. Ajoutez le gingembre et le curcuma.\n\nAjoutez les tomates concassées et le piment. Laissez réduire 5 min.\n\nIncorporez les saucisses, couvrez et laissez mijoter 25 min à feu doux.\n\nServez avec du riz blanc et un rougail tomate.",
                'imageName'   => 'rougail-saucisse.jpg',
                'imageTwoName'=> 'carryCrevette.jpg',
                'prepTime'    => 15,
                'cookTime'    => 35,
                'servings'    => 4,
                'difficulty'  => 'Facile',
                'author'      => 'admin',
                'category'    => 'reunionnais',
                'ingredientsList' => [
                    ['saucisse_fumee', 600, 'g'],
                    ['tomate',         4,   'pce'],
                    ['oignon',         2,   'pce'],
                    ['ail',            3,   'pce'],
                    ['gingembre',      1,   'cs'],
                    ['curcuma',        1,   'cc'],
                    ['huile',          3,   'cs'],
                    ['piment',         1,   'pce'],
                    ['sel',            1,   'pincee'],
                ],
                'comments' => [
                    ['user1', 'Une recette authentique ! Je la fais depuis des années et elle régale toute la famille à chaque fois.', '2026-10-05'],
                    ['user2', "J'ai ajouté un peu plus de piment, parfait pour nous ! Merci pour le partage.", '2026-10-12'],
                ],
            ],

            // ----------------------------------------------------------------
            // 2. Cari Camarons (Crevettes)
            // ----------------------------------------------------------------
            [
                'title'       => 'Cari Camarons Réunionnais',
                'slug'        => 'cari-camarons-reunionnais',
                'description' => 'Un cari de crevettes parfumé au safran pays, gingembre et tomate. La mer dans votre assiette, à la réunionnaise.',
                'instructions'=> "Décortiquez et déveinez les crevettes. Rincez et réservez.\n\nFaites suer l'oignon dans l'huile, ajoutez l'ail et le gingembre. Incorporez le safran pays.\n\nAjoutez les tomates coupées en dés et laissez compoter 8 min.\n\nAjoutez les crevettes et cuisez 5–7 min sans trop cuire.\n\nAjoutez le piment, rectifiez l'assaisonnement et servez avec du riz.",
                'imageName'   => 'cari-camarons-réunionnais.jpg',
                'imageTwoName'=> 'carryCrevette.jpg',
                'prepTime'    => 20,
                'cookTime'    => 25,
                'servings'    => 4,
                'difficulty'  => 'Facile',
                'author'      => 'admin',
                'category'    => 'reunionnais',
                'ingredientsList' => [
                    ['crevette',  500, 'g'],
                    ['tomate',    3,   'pce'],
                    ['oignon',    1,   'pce'],
                    ['ail',       2,   'pce'],
                    ['gingembre', 1,   'cs'],
                    ['safran',    1,   'cc'],
                    ['huile',     2,   'cs'],
                    ['piment',    1,   'pce'],
                    ['coriandre', 1,   'botte'],
                ],
                'comments' => [
                    ['user3', 'Magnifique recette ! Les camarons sont parfaitement cuits, ni trop ni trop peu.', '2026-09-18'],
                    ['user1', "J'ai mis du lait de coco en plus, c'était encore meilleur !", '2026-09-25'],
                ],
            ],

            // ----------------------------------------------------------------
            // 3. Carry Poulet Créole
            // ----------------------------------------------------------------
            [
                'title'       => 'Cari Poulet Créole',
                'slug'        => 'cari-poulet-creole',
                'description' => 'Le cari de poulet, plat dominical par excellence à La Réunion. Mijoté longuement avec les épices du pays pour une chair fondante et parfumée.',
                'instructions'=> "Découpez le poulet en morceaux. Faites-les dorer dans l'huile chaude.\n\nAjoutez l'oignon, l'ail et le gingembre émincés. Incorporez le curcuma et le massalé.\n\nAjoutez les tomates concassées, le piment et un verre d'eau.\n\nCouvrez et laissez mijoter 45 min à feu doux. Remuez régulièrement.\n\nServez avec du riz blanc et des grains (lentilles ou pois du cap).",
                'imageName'   => 'rougail-saucisse.jpg',
                'imageTwoName'=> 'cari-camarons-réunionnais.jpg',
                'prepTime'    => 20,
                'cookTime'    => 50,
                'servings'    => 6,
                'difficulty'  => 'Moyen',
                'author'      => 'user1',
                'category'    => 'reunionnais',
                'ingredientsList' => [
                    ['poulet',    1200, 'g'],
                    ['tomate',    3,    'pce'],
                    ['oignon',    2,    'pce'],
                    ['ail',       4,    'pce'],
                    ['gingembre', 1,    'cs'],
                    ['curcuma',   1,    'cc'],
                    ['massale',   1,    'cs'],
                    ['huile',     3,    'cs'],
                    ['piment',    1,    'pce'],
                    ['thym',      2,    'botte'],
                    ['laurier',   2,    'pce'],
                ],
                'comments' => [
                    ['user2', 'Ça sent tellement bon en cuisinant ! La texture du poulet est incroyable après 45 min de mijotage.', '2026-08-14'],
                    ['admin', "Super recette, j'ai ajouté un zeste de combava à la fin — bluffant !", '2026-08-20'],
                ],
            ],

            // ----------------------------------------------------------------
            // 4. Rougail Tomate
            // ----------------------------------------------------------------
            [
                'title'       => 'Rougail Tomate',
                'slug'        => 'rougail-tomate',
                'description' => "L'accompagnement incontournable des tables réunionnaises. Ce condiment frais à la tomate, piment et gingembre relève tous les plats.",
                'instructions'=> "Épluchez et épépinez les tomates, puis hachez-les finement.\n\nÉcrasez l'ail et le gingembre au pilon ou au hachoir.\n\nMélangez tomates, ail, gingembre, piment haché, sel et un filet d'huile.\n\nLaissez reposer 15 min avant de servir pour que les saveurs se mélangent.",
                'imageName'   => 'rougail-saucisse.jpg',
                'imageTwoName'=> 'carryCrevette.jpg',
                'prepTime'    => 15,
                'cookTime'    => 0,
                'servings'    => 4,
                'difficulty'  => 'Très facile',
                'author'      => 'user2',
                'category'    => 'entrees',
                'ingredientsList' => [
                    ['tomate',    4,  'pce'],
                    ['ail',       2,  'pce'],
                    ['gingembre', 1,  'cs'],
                    ['piment',    1,  'pce'],
                    ['huile',     1,  'cs'],
                    ['sel',       1,  'pincee'],
                    ['coriandre', 1,  'botte'],
                ],
                'comments' => [
                    ['user3', 'Indispensable sur la table ! Je mets ça sur tout, même les pâtes.', '2026-07-03'],
                    ['user1', "Excellent ! Je l'ai servi à des amis métropolitains et ils ont adoré.", '2026-07-10'],
                ],
            ],

            // ----------------------------------------------------------------
            // 5. Lentilles au Safran Pays
            // ----------------------------------------------------------------
            [
                'title'       => 'Lentilles au Safran Pays',
                'slug'        => 'lentilles-safran-pays',
                'description' => "Les lentilles créoles mijotées au safran pays (curcuma) et aux aromates. L'accompagnement idéal des caris et rougails réunionnais.",
                'instructions'=> "Rincez les lentilles. Faites revenir l'oignon et l'ail dans l'huile.\n\nAjoutez le safran pays, le thym et les lentilles. Couvrez d'eau à hauteur.\n\nLaissez cuire 20–25 min à feu moyen. Ajoutez le piment en fin de cuisson.\n\nRectifiez le sel et servez en accompagnement.",
                'imageName'   => 'cari-camarons-réunionnais.jpg',
                'imageTwoName'=> 'rougail-saucisse.jpg',
                'prepTime'    => 10,
                'cookTime'    => 25,
                'servings'    => 4,
                'difficulty'  => 'Facile',
                'author'      => 'user3',
                'category'    => 'vegetarien',
                'ingredientsList' => [
                    ['lentille', 300, 'g'],
                    ['oignon',   1,   'pce'],
                    ['ail',      2,   'pce'],
                    ['safran',   1,   'cc'],
                    ['thym',     1,   'botte'],
                    ['piment',   1,   'pce'],
                    ['huile',    2,   'cs'],
                    ['sel',      1,   'pincee'],
                ],
                'comments' => [
                    ['admin', 'Je ne pensais pas que les lentilles pouvaient être aussi savoureuses ! Recette adoptée.', '2026-06-22'],
                    ['user2', 'Parfait avec un cari de poulet. La couleur est magnifique grâce au safran.', '2026-06-30'],
                ],
            ],

            // ----------------------------------------------------------------
            // 6. Cari Bœuf Mijoté
            // ----------------------------------------------------------------
            [
                'title'       => 'Cari Bœuf Mijoté',
                'slug'        => 'cari-boeuf-mijote',
                'description' => 'Un cari de bœuf longuement mijoté, fondant à souhait. Les épices créoles transforment ce plat en véritable festin.',
                'instructions'=> "Découpez le bœuf en cubes. Faites-les dorer sur toutes les faces dans l'huile très chaude.\n\nAjoutez l'oignon, l'ail, le gingembre et les épices. Mélangez bien.\n\nAjoutez les tomates et 20 cl d'eau. Couvrez et laissez mijoter 1h30.\n\nVérifiez la tendreté de la viande. Rectifiez assaisonnement et servez.",
                'imageName'   => 'Steak-boeuf.jpg',
                'imageTwoName'=> 'rougail-saucisse.jpg',
                'prepTime'    => 20,
                'cookTime'    => 100,
                'servings'    => 4,
                'difficulty'  => 'Moyen',
                'author'      => 'admin',
                'category'    => 'reunionnais',
                'ingredientsList' => [
                    ['boeuf',     800, 'g'],
                    ['tomate',    3,   'pce'],
                    ['oignon',    2,   'pce'],
                    ['ail',       3,   'pce'],
                    ['gingembre', 1,   'cs'],
                    ['massale',   1,   'cs'],
                    ['curcuma',   0.5, 'cc'],
                    ['huile',     3,   'cs'],
                    ['thym',      2,   'botte'],
                    ['piment',    1,   'pce'],
                ],
                'comments' => [
                    ['user1', 'La viande est tellement fondante après 1h30 ! Le massalé donne un arôme unique.', '2026-11-01'],
                    ['user3', 'Recette réalisée pour un repas de famille, tout le monde en a repris deux fois.', '2026-11-05'],
                ],
            ],

            // ----------------------------------------------------------------
            // 7. Achards de Légumes
            // ----------------------------------------------------------------
            [
                'title'       => 'Achards de Légumes',
                'slug'        => 'achards-de-legumes',
                'description' => "Les achards, cet accompagnement croquant et épicé à base de légumes marinés dans les épices. Une explosion de saveurs et de couleurs.",
                'instructions'=> "Râpez ou taillez en julienne : carotte, chou et haricots verts (blanchis 3 min).\n\nFaites revenir l'ail et le gingembre dans l'huile, ajoutez le curcuma et le piment.\n\nAjoutez les légumes, mélangez bien et cuisez 5 min en remuant.\n\nAjoutez un filet de citron, rectifiez le sel. Laissez refroidir avant de servir.",
                'imageName'   => 'cari-camarons-réunionnais.jpg',
                'imageTwoName'=> 'carryCrevette.jpg',
                'prepTime'    => 25,
                'cookTime'    => 10,
                'servings'    => 6,
                'difficulty'  => 'Facile',
                'author'      => 'user2',
                'category'    => 'entrees',
                'ingredientsList' => [
                    ['carotte',      2,   'pce'],
                    ['haricot_vert', 200, 'g'],
                    ['chou',         200, 'g'],
                    ['ail',          3,   'pce'],
                    ['gingembre',    1,   'cs'],
                    ['curcuma',      1,   'cc'],
                    ['piment',       1,   'pce'],
                    ['citron',       1,   'pce'],
                    ['huile',        3,   'cs'],
                    ['sel',          1,   'pincee'],
                ],
                'comments' => [
                    ['user1', 'Croquants et bien relevés ! Je les sers en entrée et tout le monde les adore.', '2026-10-14'],
                    ['admin', "J'ajoute aussi des haricots verts fins, c'est encore meilleur.", '2026-10-19'],
                ],
            ],

            // ----------------------------------------------------------------
            // 8. Salade de Fruits Exotiques
            // ----------------------------------------------------------------
            [
                'title'       => 'Salade de Fruits Exotiques',
                'slug'        => 'salade-fruits-exotiques',
                'description' => 'Une salade de fruits frais parfumée au rhum et à la vanille de La Réunion. Le dessert léger et rafraîchissant par excellence sous le soleil créole.',
                'instructions'=> "Épluchez et découpez en morceaux : banane, ananas et carotte râpée.\n\nMélangez dans un saladier avec le sucre, l'extrait de vanille et un trait de rhum.\n\nLaissez macérer 20 min au réfrigérateur avant de servir bien frais.",
                'imageName'   => 'saladeFruit.jpg',
                'imageTwoName'=> 'saladeFruit.jpg',
                'prepTime'    => 15,
                'cookTime'    => 0,
                'servings'    => 4,
                'difficulty'  => 'Très facile',
                'author'      => 'user3',
                'category'    => 'desserts',
                'ingredientsList' => [
                    ['banane',  3,  'pce'],
                    ['ananas',  1,  'pce'],
                    ['citron',  1,  'pce'],
                    ['sucre',   2,  'cs'],
                    ['vanille', 1,  'cc'],
                    ['rhum',    2,  'cs'],
                ],
                'comments' => [
                    ['user2', 'Fraîcheur garantie ! Le rhum et la vanille font toute la différence.', '2026-07-20'],
                    ['user1', "J'ai rajouté de la mangue, un délice tropical complet !", '2026-07-28'],
                ],
            ],

            // ----------------------------------------------------------------
            // 9. Gâteau Patate Réunionnais
            // ----------------------------------------------------------------
            [
                'title'       => 'Gâteau Patate Réunionnais',
                'slug'        => 'gateau-patate-reunionnais',
                'description' => 'Le gâteau patate, douceur créole traditionnelle à base de patate douce, parfumée à la vanille et au rhum. Un classique des fêtes réunionnaises.',
                'instructions'=> "Faites cuire la patate douce à la vapeur, puis écrasez-la en purée lisse.\n\nMélangez avec le sucre, les œufs, le beurre fondu, la vanille et le rhum.\n\nAjoutez la farine progressivement jusqu'à obtenir une pâte homogène.\n\nVersez dans un moule beurré et enfournez 35 min à 180°C.\n\nLaissez refroidir avant de démouler.",
                'imageName'   => 'chocolateCakeaRogner.jpg',
                'imageTwoName'=> 'saladeFruit.jpg',
                'prepTime'    => 20,
                'cookTime'    => 35,
                'servings'    => 8,
                'difficulty'  => 'Facile',
                'author'      => 'user1',
                'category'    => 'desserts',
                'ingredientsList' => [
                    ['oeuf',    3,   'pce'],
                    ['sucre',   150, 'g'],
                    ['beurre',  80,  'g'],
                    ['farine',  100, 'g'],
                    ['vanille', 1,   'cc'],
                    ['rhum',    2,   'cs'],
                    ['lait',    10,  'cl'],
                ],
                'comments' => [
                    ['user3', "Moelleux, parfumé, fondant… Je ne m'attendais pas à un si beau résultat !", '2026-05-11'],
                    ['admin', "Un dessert qui me rappelle l'enfance à La Réunion. Merci pour ce partage.", '2026-05-15'],
                ],
            ],

            // ----------------------------------------------------------------
            // 10. Thon à la Créole
            // ----------------------------------------------------------------
            [
                'title'       => 'Thon à la Créole',
                'slug'        => 'thon-a-la-creole',
                'description' => 'Un plat de thon frais mijoté dans une sauce créole tomate-épices. Simple, savoureux et profondément réunionnais.',
                'instructions'=> "Découpez le thon en pavés épais. Faites-les mariner 30 min avec citron, sel et poivre.\n\nFaites dorer les pavés 2 min de chaque côté dans l'huile. Réservez.\n\nFaites la sauce : oignon, ail, tomate, curcuma, piment. Laissez compoter 10 min.\n\nRemettez le thon dans la sauce et laissez cuire 5 min à feu doux.\n\nServez avec du riz blanc.",
                'imageName'   => 'shopsuiPoissson.jpg',
                'imageTwoName'=> 'shopsuiSesame.jpg',
                'prepTime'    => 40,
                'cookTime'    => 20,
                'servings'    => 4,
                'difficulty'  => 'Moyen',
                'author'      => 'admin',
                'category'    => 'reunionnais',
                'ingredientsList' => [
                    ['thon',      600, 'g'],
                    ['tomate',    3,   'pce'],
                    ['oignon',    1,   'pce'],
                    ['ail',       2,   'pce'],
                    ['curcuma',   1,   'cc'],
                    ['piment',    1,   'pce'],
                    ['citron',    1,   'pce'],
                    ['huile',     3,   'cs'],
                    ['sel',       1,   'pincee'],
                    ['coriandre', 1,   'botte'],
                ],
                'comments' => [
                    ['user2', "Le thon frais comme ça, c'est un délice. La sauce créole est parfaite.", '2026-09-02'],
                    ['user3', "J'ai utilisé du thon albacore, excellent ! La marinade au citron est clé.", '2026-09-08'],
                ],
            ],

            // ----------------------------------------------------------------
            // 11. Cari Porc au Chou
            // ----------------------------------------------------------------
            [
                'title'       => 'Cari Porc au Chou',
                'slug'        => 'cari-porc-au-chou',
                'description' => 'Un cari de porc savoureux mêlé de chou. Doux, épicé et parfumé aux épices créoles, ce plat familial est un réel réconfort.',
                'instructions'=> "Découpez le porc en cubes et faites-le dorer dans l'huile chaude.\n\nAjoutez l'oignon, l'ail, le gingembre et les épices. Mélangez 2 min.\n\nAjoutez les tomates et le chou découpé en morceaux. Versez 15 cl d'eau.\n\nCouvrez et laissez cuire 50 min à feu doux.\n\nServez avec du riz et du rougail tomate.",
                'imageName'   => 'rougail-saucisse.jpg',
                'imageTwoName'=> 'cari-camarons-réunionnais.jpg',
                'prepTime'    => 20,
                'cookTime'    => 55,
                'servings'    => 4,
                'difficulty'  => 'Facile',
                'author'      => 'user2',
                'category'    => 'reunionnais',
                'ingredientsList' => [
                    ['porc',      700, 'g'],
                    ['chou',      300, 'g'],
                    ['tomate',    2,   'pce'],
                    ['oignon',    2,   'pce'],
                    ['ail',       3,   'pce'],
                    ['gingembre', 1,   'cs'],
                    ['curcuma',   1,   'cc'],
                    ['massale',   1,   'cs'],
                    ['huile',     2,   'cs'],
                    ['piment',    1,   'pce'],
                ],
                'comments' => [
                    ['user1', "Le chou s'imprègne tellement bien des épices. Un vrai plat du dimanche.", '2026-08-03'],
                    ['user3', 'Premier essai réussi haut la main. Toute la maisonnée en a repris !', '2026-08-09'],
                ],
            ],

            // ----------------------------------------------------------------
            // 12. Morue Sautée à la Créole
            // ----------------------------------------------------------------
            [
                'title'       => 'Morue Sautée à la Créole',
                'slug'        => 'morue-sautee-creole',
                'description' => 'La morue (cabillaud salé) dessalée puis sautée aux épices créoles. Un plat traditionnel et économique, profondément ancré dans la culture réunionnaise.',
                'instructions'=> "Dessalez la morue 24h à l'avance en changeant l'eau 3 fois.\n\nÉgrenez la morue. Faites revenir l'oignon et l'ail dans l'huile.\n\nAjoutez la morue, le curcuma, le piment et les tomates. Faites sauter 10 min.\n\nAjustez le sel si nécessaire. Servez avec du riz ou de la fécule.",
                'imageName'   => 'shopsuiPoissson.jpg',
                'imageTwoName'=> 'rougail-saucisse.jpg',
                'prepTime'    => 30,
                'cookTime'    => 15,
                'servings'    => 4,
                'difficulty'  => 'Facile',
                'author'      => 'user3',
                'category'    => 'reunionnais',
                'ingredientsList' => [
                    ['morue',     500, 'g'],
                    ['tomate',    2,   'pce'],
                    ['oignon',    1,   'pce'],
                    ['ail',       2,   'pce'],
                    ['curcuma',   1,   'cc'],
                    ['piment',    1,   'pce'],
                    ['huile',     3,   'cs'],
                    ['coriandre', 1,   'botte'],
                ],
                'comments' => [
                    ['admin', "Penser à bien dessaler la morue, c'est la clé ! Recette très fidèle à l'originale.", '2026-07-15'],
                    ['user2', 'Délicieux et nostalgique. Ça me rappelle les repas du vendredi chez ma grand-mère.', '2026-07-21'],
                ],
            ],

            // ----------------------------------------------------------------
            // 13. Omelette Créole au Rougail
            // ----------------------------------------------------------------
            [
                'title'       => 'Omelette Créole au Rougail',
                'slug'        => 'omelette-creole-rougail',
                'description' => 'Une omelette moelleuse garnie de rougail tomate, piment et coriandre. Rapide, savoureuse et 100% créole.',
                'instructions'=> "Battez les œufs avec une pincée de sel et de poivre.\n\nFaites revenir l'oignon et la tomate hachée dans l'huile. Ajoutez le piment et la coriandre.\n\nVersez les œufs sur la garniture. Cuisez à feu moyen jusqu'à cuisson souhaitée.\n\nPliez et servez immédiatement avec du riz ou du pain.",
                'imageName'   => 'omelette.jpg',
                'imageTwoName'=> 'rougail-saucisse.jpg',
                'prepTime'    => 10,
                'cookTime'    => 10,
                'servings'    => 2,
                'difficulty'  => 'Très facile',
                'author'      => 'user1',
                'category'    => 'brunch',
                'ingredientsList' => [
                    ['oeuf',      4,   'pce'],
                    ['tomate',    1,   'pce'],
                    ['oignon',    1,   'pce'],
                    ['piment',    1,   'pce'],
                    ['coriandre', 0.5, 'botte'],
                    ['huile',     1,   'cs'],
                    ['sel',       1,   'pincee'],
                    ['poivre',    1,   'pincee'],
                ],
                'comments' => [
                    ['user2', "Prête en 20 min, et on se croirait en vacances à La Réunion !", '2026-10-30'],
                    ['user3', "J'ai ajouté un peu de fromage râpé, encore meilleure comme ça.", '2026-11-02'],
                ],
            ],

            // ----------------------------------------------------------------
            // 14. Pois du Cap à la Réunionnaise
            // ----------------------------------------------------------------
            [
                'title'       => 'Pois du Cap à la Réunionnaise',
                'slug'        => 'pois-du-cap-reunionnais',
                'description' => "Les pois du Cap mijotés à la réunionnaise avec ail, oignon et safran pays. L'accompagnement idéal des caris créoles.",
                'instructions'=> "Faites tremper les pois du cap la veille. Cuisez-les 1h dans l'eau salée.\n\nFaites revenir oignon et ail dans l'huile. Ajoutez le safran pays.\n\nAjoutez les pois égouttés et un peu d'eau de cuisson. Mijotez 20 min.\n\nAjoutez le piment, rectifiez le sel et servez.",
                'imageName'   => 'cari-camarons-réunionnais.jpg',
                'imageTwoName'=> 'rougail-saucisse.jpg',
                'prepTime'    => 20,
                'cookTime'    => 80,
                'servings'    => 6,
                'difficulty'  => 'Facile',
                'author'      => 'user3',
                'category'    => 'vegetarien',
                'ingredientsList' => [
                    ['pois_du_cap', 400, 'g'],
                    ['oignon',      1,   'pce'],
                    ['ail',         3,   'pce'],
                    ['safran',      1,   'cc'],
                    ['piment',      1,   'pce'],
                    ['huile',       2,   'cs'],
                    ['sel',         1,   'pincee'],
                    ['thym',        1,   'botte'],
                ],
                'comments' => [
                    ['user1', 'On ne peut pas manger un cari sans ces pois du cap ! Recette simple et parfaite.', '2026-09-11'],
                    ['admin', "J'utilise des pois du cap frais quand c'est la saison. Encore meilleur.", '2026-09-17'],
                ],
            ],

            // ----------------------------------------------------------------
            // 15. Cake au Chocolat Noir Fondant
            // ----------------------------------------------------------------
            [
                'title'       => 'Cake au Chocolat Noir Fondant',
                'slug'        => 'cake-chocolat-noir-fondant',
                'description' => 'Un cake au chocolat noir ultra fondant, réalisé avec du chocolat noir de qualité. Le dessert gourmand pour clore un repas créole en beauté.',
                'instructions'=> "Faites fondre le chocolat et le beurre au bain-marie. Mélangez jusqu'à obtenir une texture lisse.\n\nBattez les œufs avec le sucre jusqu'à blanchissement. Incorporez le chocolat fondu.\n\nAjoutez la farine tamisée et mélangez délicatement.\n\nVersez dans un moule beurré et cuisez 25–30 min à 170°C.\n\nLaissez refroidir 10 min avant de démouler.",
                'imageName'   => 'chocolateCakeaRogner.jpg',
                'imageTwoName'=> 'eclairChocolat.jpg',
                'prepTime'    => 15,
                'cookTime'    => 30,
                'servings'    => 8,
                'difficulty'  => 'Facile',
                'author'      => 'user2',
                'category'    => 'desserts',
                'ingredientsList' => [
                    ['chocolat', 200, 'g'],
                    ['beurre',   125, 'g'],
                    ['sucre',    150, 'g'],
                    ['oeuf',     4,   'pce'],
                    ['farine',   80,  'g'],
                    ['sel',      1,   'pincee'],
                    ['vanille',  1,   'cc'],
                ],
                'comments' => [
                    ['user1', 'Fondant à cœur, croûte légèrement craquante… La recette de cake au chocolat ultime !', '2026-11-10'],
                    ['user3', "Réalisé pour l'anniversaire de ma fille, elle a adoré. Merci pour cette recette !", '2026-11-13'],
                ],
            ],
        ];

        // -----------------------------------------------------------------
        // Boucle de création : hydrate et persiste chaque recette
        // avec ses RecipeIngredient et ses Comment associés
        // -----------------------------------------------------------------
        $createdRecipes = [];
        foreach ($recipesData as $data) {

            // === Recette principale ===
            $recipe = new Recipe();
            $recipe->setTitle($data['title'])
                   ->setSlug($data['slug'])
                   ->setDescription($data['description'])
                   ->setInstructions($data['instructions'])
                   ->setImageName($data['imageName'])
                   ->setImageTwoName($data['imageTwoName'])
                   ->setPrepTime($data['prepTime'])
                   ->setCookTime($data['cookTime'])
                   ->setServings($data['servings'])
                   ->setDifficulty($data['difficulty'])
                   ->setAuthor($users[$data['author']])
                   ->setCategory($categories[$data['category']])
                   ->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($recipe);

            // === Ingrédients liés à la recette (table pivot RecipeIngredient) ===
            foreach ($data['ingredientsList'] as [$ingredientKey, $quantity, $unitKey]) {
                $ri = new RecipeIngredient();
                $ri->setRecipe($recipe)
                   ->setIngredient($ingredients[$ingredientKey])
                   ->setQuantity((float) $quantity)
                   ->setUnit($units[$unitKey]);
                $manager->persist($ri);
            }

            // === Commentaires de la recette ===
            foreach ($data['comments'] as [$authorKey, $content, $dateStr]) {
                $comment = new Comment();
                $comment->setContent($content)
                        ->setAuthor($users[$authorKey])
                        ->setRecipe($recipe)
                        ->setCreatedAt(new \DateTimeImmutable($dateStr));
                $manager->persist($comment);
            }

            $createdRecipes[] = $recipe;
        }

        // === Likes de démonstration (pour tester le filtre "Mes likes / Favoris") ===
        // On associe plusieurs likes aux utilisateurs de test
        $likesMap = [
            'admin' => [0, 1, 2, 5, 8],     // L'admin aime 5 recettes
            'user1' => [0, 1, 3, 7, 14],    // user1 aime 5 recettes
            'user2' => [1, 4, 6, 10],       // user2 aime 4 recettes
            'user3' => [2, 3, 8, 12, 14],   // user3 aime 5 recettes
        ];

        foreach ($likesMap as $userKey => $recipeIndexes) {
            foreach ($recipeIndexes as $index) {
                if (isset($createdRecipes[$index])) {
                    $like = new RecipeLike();
                    $like->setUser($users[$userKey])
                         ->setRecipe($createdRecipes[$index])
                         ->setCreatedAt(new \DateTimeImmutable());
                    $manager->persist($like);
                }
            }
        }
    }
}
