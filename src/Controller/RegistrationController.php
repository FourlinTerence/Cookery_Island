<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant l'inscription des nouveaux utilisateurs.
 * 
 * Permet aux visiteurs de créer un compte avec un pseudonyme, une adresse email
 * et un mot de passe sécurisé conformément aux spécifications du projet.
 */
class RegistrationController extends AbstractController
{
    /**
     * Affiche le formulaire d'inscription et traite la soumission des données.
     * 
     * @param Request $request Requête HTTP entrante
     * @param UserPasswordHasherInterface $userPasswordHasher Service de hachage des mots de passe
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @return Response Vue Twig du formulaire d'inscription ou redirection
     */
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Redirige l'utilisateur s'il est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $error = null;

        // Traitement de la soumission du formulaire
        if ($request->isMethod('POST')) {
            $token = $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('register', $token)) {
                $error = 'Jeton de sécurité CSRF invalide. Veuillez réessayer.';
            } else {
                $username = trim((string) $request->request->get('username'));
                $email = trim((string) $request->request->get('email'));
                $plainPassword = (string) $request->request->get('password');
                $confirmPassword = (string) $request->request->get('confirm_password');
                $agreeTerms = (bool) $request->request->get('agreeTerms');

                // Validation élémentaire des champs
                if (empty($username) || empty($email) || empty($plainPassword)) {
                    $error = 'Tous les champs obligatoires doivent être renseignés.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'L\'adresse email saisie n\'est pas valide.';
                } elseif (strlen($plainPassword) < 6) {
                    $error = 'Le mot de passe doit comporter au moins 6 caractères.';
                } elseif ($plainPassword !== $confirmPassword) {
                    $error = 'Les deux mots de passe ne correspondent pas.';
                } elseif (!$agreeTerms) {
                    $error = 'Veuillez accepter les conditions générales d\'utilisation.';
                } else {
                    // Vérification de l'unicité de l'email
                    $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                    if ($existingUser) {
                        $error = 'Un compte existe déjà avec cette adresse email.';
                    } else {
                        // Création du nouvel utilisateur
                        $user = new User();
                        $user->setUsername($username);
                        $user->setEmail($email);
                        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                        $user->setRoles(['ROLE_USER']);

                        $entityManager->persist($user);
                        $entityManager->flush();

                        $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');

                        return $this->redirectToRoute('app_login');
                    }
                }
            }
        }

        return $this->render('registration/register.html.twig', [
            'error' => $error,
            'last_username' => $request->request->get('username', ''),
            'last_email' => $request->request->get('email', ''),
        ]);
    }
}
