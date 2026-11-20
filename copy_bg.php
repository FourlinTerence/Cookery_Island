<?php
/**
 * Script utilitaire ponctuel — copie backgroundHeader.jpg dans public/images/
 * À exécuter une seule fois puis à supprimer.
 *
 * Usage : php copy_bg.php
 */

$src  = __DIR__ . '/assets/images/backgroundHeader.jpg';
$dest = __DIR__ . '/public/images/backgroundHeader.jpg';

// Crée le dossier public/images/ s'il n'existe pas
if (!is_dir(dirname($dest))) {
    mkdir(dirname($dest), 0755, true);
    echo "Dossier public/images/ créé.\n";
}

if (!is_file($src)) {
    echo "ERREUR : fichier source introuvable : $src\n";
    exit(1);
}

if (copy($src, $dest)) {
    echo "OK : backgroundHeader.jpg copié dans public/images/\n";
} else {
    echo "ERREUR : échec de la copie.\n";
    exit(1);
}
