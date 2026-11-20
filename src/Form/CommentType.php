<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire de rédaction d'un commentaire sur une recette.
 *
 * Conforme aux règles d'authentification :
 * Le champ "pseudo" est délibérément omis car l'utilisateur doit être connecté
 * pour poster. L'auteur du commentaire est automatiquement associé à l'utilisateur
 * en session (sécurité anti-usurpation et meilleure expérience utilisateur).
 */
class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Votre avis ou astuce sur la recette',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Partagez vos impressions, vos ajustements d\'épices ou vos variantes gourmandes...',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir votre commentaire avant de publier.',
                    ),
                    new Length(
                        min: 5,
                        max: 2000,
                        minMessage: 'Votre commentaire doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'Votre commentaire ne peut pas dépasser {{ limit }} caractères.',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
