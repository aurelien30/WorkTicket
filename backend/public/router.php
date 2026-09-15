<?php

// Sert les fichiers statiques (images, CSS, JS...) directement,
// sans les faire passer par le Kernel Symfony.
$staticExtensions = '/\.(?:png|jpg|jpeg|gif|svg|webp|ico|css|js|woff|woff2|ttf|map)$/';

if (preg_match($staticExtensions, $_SERVER['REQUEST_URI'])) {
    return false; // false = "sers ce fichier tel quel", géré nativement par PHP
}

require __DIR__ . '/index.php';