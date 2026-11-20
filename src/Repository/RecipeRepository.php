<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * RecipeRepository — Gestion des requêtes de sélection et de filtrage des recettes.
 *
 * Fournit des méthodes pour la recherche avancée avec pagination et filtres multicritères :
 * - Temps total (préparation + cuisson) séparé en Rapide / Long
 * - Difficulté (Très facile, Facile, Moyen, Difficile)
 * - Favoris / Likes si l'utilisateur est connecté
 * - Catégorie culinaire et recherche textuelle
 *
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * Recherche paginée de recettes avec filtres multicritères.
     *
     * @param array{
     *     search?: string|null,
     *     category?: string|null,
     *     difficulty?: string|null,
     *     duration?: string|null,
     *     liked?: bool|string|null,
     *     sort?: string|null
     * } $filters Filtres transmis par la requête HTTP
     * @param int       $page        Numéro de la page demandée (≥ 1)
     * @param int       $limit       Nombre maximal de recettes par page (9 par défaut)
     * @param User|null $currentUser Utilisateur actuellement connecté (pour le filtre des likes)
     *
     * @return Paginator Paginator Doctrine contenant les résultats et les métadonnées de pagination
     */
    public function findPaginatedRecipes(
        array $filters = [],
        int $page = 1,
        int $limit = 9,
        ?User $currentUser = null
    ): Paginator {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.category', 'c')
            ->addSelect('c')
            ->leftJoin('r.author', 'a')
            ->addSelect('a')
            ->leftJoin('r.recipeLikes', 'rl')
            ->addSelect('rl');

        // --- 1. Filtre recherche textuelle (titre ou description) ---
        if (!empty($filters['search'])) {
            $qb->andWhere('LOWER(r.title) LIKE :search OR LOWER(r.description) LIKE :search')
               ->setParameter('search', '%' . mb_strtolower(trim((string)$filters['search'])) . '%');
        }

        // --- 2. Filtre par catégorie (slug ou id) ---
        if (!empty($filters['category'])) {
            $qb->andWhere('c.slug = :category OR c.id = :category')
               ->setParameter('category', $filters['category']);
        }

        // --- 3. Filtre par difficulté ---
        if (!empty($filters['difficulty'])) {
            $qb->andWhere('r.difficulty = :difficulty')
               ->setParameter('difficulty', $filters['difficulty']);
        }

        // --- 4. Filtre par temps total (temps de préparation + temps de cuisson) ---
        // 'fast' : rapide (≤ 40 minutes au total)
        // 'long' : long (> 40 minutes au total)
        if (!empty($filters['duration'])) {
            $durationFilter = (string)$filters['duration'];
            if ($durationFilter === 'fast') {
                $qb->andWhere('(COALESCE(r.prepTime, 0) + COALESCE(r.cookTime, 0)) <= :maxFastTime')
                   ->setParameter('maxFastTime', 40);
            } elseif ($durationFilter === 'long') {
                $qb->andWhere('(COALESCE(r.prepTime, 0) + COALESCE(r.cookTime, 0)) > :minLongTime')
                   ->setParameter('minLongTime', 40);
            }
        }

        // --- 5. Filtre par "Mes likes / Favoris" (uniquement si utilisateur connecté) ---
        $likedOnly = !empty($filters['liked']) && in_array($filters['liked'], [true, 1, '1', 'true', 'on'], true);
        if ($likedOnly && $currentUser !== null) {
            $qb->innerJoin('r.recipeLikes', 'user_like')
               ->andWhere('user_like.user = :currentUser')
               ->setParameter('currentUser', $currentUser);
        }

        // --- 6. Tri des recettes ---
        // Par défaut : du plus récent au plus ancien
        $sort = $filters['sort'] ?? 'newest';
        match ($sort) {
            'oldest' => $qb->orderBy('r.createdAt', 'ASC'),
            'prep_asc' => $qb->orderBy('(COALESCE(r.prepTime, 0) + COALESCE(r.cookTime, 0))', 'ASC'),
            'prep_desc' => $qb->orderBy('(COALESCE(r.prepTime, 0) + COALESCE(r.cookTime, 0))', 'DESC'),
            'title_asc' => $qb->orderBy('r.title', 'ASC'),
            default => $qb->orderBy('r.createdAt', 'DESC'),
        };

        // --- 7. Configuration de la pagination ---
        $page = max(1, $page);
        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return new Paginator($qb, fetchJoinCollection: true);
    }
}
