<?php

// Sauvegarde du fichier .env.local
// copy(__DIR__.'/../.env.local', '/tmp/.env.local.bak');

// pour éviter de sauvegarder .env.local faire la commande : chmod 600 .env.local
// il ne sera pas écrasé par le git pull car  lecture et écriture uniquement pour l'utilisateur système 
// pour revenir en arrière et lui redonner ses droits d'origine : chmod 644 .env.local
   

// Récupération de la dernière version de la branche "main"
exec('git fetch origin main');
exec('git reset --hard origin/main');

// Installation des dépendances
exec('composer install');

// Mise à jour de la base de données
exec('php bin/console doctrine:migrations:migrate --no-interaction');

// Restauration du fichier .env.local
// copy('/tmp/.env.local.bak', '.env.local');

