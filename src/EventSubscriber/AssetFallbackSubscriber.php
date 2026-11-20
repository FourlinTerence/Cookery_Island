<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * AssetFallbackSubscriber — Gestionnaire de secours pour les assets statiques.
 *
 * Rôle & Justification de conception :
 * Symfony AssetMapper utilise par défaut des chemins versionnés avec empreinte numérique (digest).
 * Les feuilles de style CSS utilisant la fonction standard `url('../images/...')` peuvent émettre
 * des requêtes directes sans empreinte (ex: `/assets/images/backgroundHeader.jpg`).
 *
 * Ce subscriber intercepte ces requêtes avec une priorité supérieure (36) à celle
 * du serveur de développement interne d'AssetMapper (35), afin de servir directement
 * le fichier binaire correspondant depuis le répertoire `assets/` sans déclencher d'erreur 404.
 */
class AssetFallbackSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $pathInfo = rawurldecode($request->getPathInfo());

        // Traite les requêtes ciblant les ressources sous /assets/
        if (str_starts_with($pathInfo, '/assets/')) {
            $relativePath = substr($pathInfo, \strlen('/assets/'));

            // Sécurité : interdire les traversées de répertoire
            if (str_contains($relativePath, '..')) {
                return;
            }

            $filePath = $this->projectDir . '/assets/' . $relativePath;

            if (is_file($filePath)) {
                // Retourne directement le fichier binaire avec les en-têtes MIME adéquats
                $response = new BinaryFileResponse($filePath);
                $response->setAutoLastModified();
                $event->setResponse($response);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Priorité 36 : exécuté juste avant AssetMapperDevServerSubscriber (priorité 35)
            KernelEvents::REQUEST => [['onKernelRequest', 36]],
        ];
    }
}
