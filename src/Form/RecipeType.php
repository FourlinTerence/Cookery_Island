<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Recipe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * RecipeType — Formulaire Symfony dédié à la création et publication d'une recette culinaire.
 *
 * Utilise la syntaxe moderne des contraintes de validation Symfony (arguments nommés PHP 8+).
 */
class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // --- 1. Titre de la recette ---
            ->add('title', TextType::class, [
                'label' => 'Titre de la recette',
                'attr' => [
                    'placeholder' => 'ex: Cari camarons traditionnel, Rougail saucisse fumé...',
                    'class' => 'form-input',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir un titre pour votre recette.'
                    ),
                    new Length(
                        min: 3,
                        max: 255,
                        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
                    ),
                ],
            ])

            // --- 2. Catégorie culinaire ---
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'placeholder' => 'Sélectionnez une catégorie',
                'required' => false,
                'attr' => [
                    'class' => 'form-select',
                ],
            ])

            // --- 3. Courte description / introduction ---
            ->add('description', TextareaType::class, [
                'label' => 'Courte description',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Quelques mots pour donner l\'eau à la bouche et raconter l\'histoire ou les arômes de votre plat...',
                    'class' => 'form-textarea',
                ],
                'constraints' => [
                    new Length(
                        max: 1000,
                        maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.'
                    ),
                ],
            ])

            // --- 4. Temps de préparation (en minutes) ---
            ->add('prepTime', IntegerType::class, [
                'label' => 'Préparation (min)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ex: 20',
                    'min' => 1,
                    'max' => 1440,
                    'class' => 'form-input',
                ],
                'constraints' => [
                    new Positive(
                        message: 'Le temps de préparation doit être un nombre positif.'
                    ),
                ],
            ])

            // --- 5. Temps de cuisson (en minutes) ---
            ->add('cookTime', IntegerType::class, [
                'label' => 'Cuisson (min)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ex: 35',
                    'min' => 0,
                    'max' => 1440,
                    'class' => 'form-input',
                ],
                'constraints' => [
                    new PositiveOrZero(
                        message: 'Le temps de cuisson doit être supérieur ou égal à 0.'
                    ),
                ],
            ])

            // --- 6. Nombre de portions / personnes ---
            ->add('servings', IntegerType::class, [
                'label' => 'Portions (personnes)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ex: 4',
                    'min' => 1,
                    'max' => 100,
                    'class' => 'form-input',
                ],
                'constraints' => [
                    new Positive(
                        message: 'Le nombre de portions doit être au moins 1 personne.'
                    ),
                ],
            ])

            // --- 7. Niveau de difficulté ---
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulté',
                'required' => false,
                'placeholder' => 'Sélectionnez un niveau',
                'choices' => [
                    'Très facile' => 'Très facile',
                    'Facile'      => 'Facile',
                    'Moyen'       => 'Moyen',
                    'Difficile'   => 'Difficile',
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
            ])

            // --- 8. Étapes de préparation / Instructions détaillées ---
            ->add('instructions', TextareaType::class, [
                'label' => 'Étapes de préparation',
                'help' => 'Astuce : séparez chaque étape par une ligne vide pour qu\'elle apparaisse sous forme d\'étape numérotée dans la vue détaillée.',
                'attr' => [
                    'rows' => 8,
                    'placeholder' => "1. Émincez finement les oignons et pilez l'ail avec le gingembre et le piment...\n\n2. Faites dorer les morceaux dans une cocotte en fonte avec l'huile bien chaude...\n\n3. Incorporez les tomates concassées et le curcuma, puis laissez mijoter à feu doux...",
                    'class' => 'form-textarea',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez rédiger les étapes de préparation de votre recette.'
                    ),
                    new Length(
                        min: 10,
                        minMessage: 'Les instructions doivent contenir au moins {{ limit }} caractères.'
                    ),
                ],
            ])

            // --- 9. Photo principale de la recette (upload optionnel) ---
            ->add('imageFile', FileType::class, [
                'label' => 'Photo du plat (JPEG, PNG ou WEBP)',
                'mapped' => false,
                'required' => false,
                'help' => 'Taille maximale : 5 Mo. Formats acceptés : JPG, PNG, WEBP.',
                'attr' => [
                    'class' => 'form-file-input',
                    'accept' => 'image/jpeg,image/png,image/webp',
                ],
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        maxSizeMessage: 'L\'image ne doit pas dépasser 5 Mo.',
                        mimeTypesMessage: 'Veuillez télécharger un format d\'image valide (JPEG, PNG ou WEBP).'
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
