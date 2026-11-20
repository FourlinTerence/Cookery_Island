<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur dédié à la gestion de la sécurité et de l'authentification.
 * 
 * Implémente le flux standard de connexion et de déconnexion sous Symfony 8.1.
 * Respecte l'architecture du projet Cookery Island et les conventions du framework.
 */
class SecurityController extends AbstractController
{
    /**
     * Affiche la page de connexion et gère la restitution des erreurs éventuelles.
     * 
     * @param AuthenticationUtils $authenticationUtils Utilitaire Symfony pour extraire les erreurs et le dernier identifiant
     * @return Response Réponse HTTP rendant la vue Twig
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère l'erreur de connexion s'il y en a une (ex: mauvais mot de passe)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier identifiant (email) saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Route de déconnexion.
     * 
     * Cette méthode ne contient pas de code métier direct car son exécution
     * est interceptée en amont par le composant de sécurité (clé "logout" du firewall).
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode est interceptée par le pare-feu de sécurité (firewall) de Symfony.');
    }
}
