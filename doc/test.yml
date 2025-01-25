Voici les étapes pour créer et gérer une clé SSH pour GitHub et pour votre serveur :

Création de la clé SSH pour GitHub

Ouvrez un terminal sur votre ordinateur.
Tapez la commande suivante : ssh-keygen -t ed25519 -C "votre_email@example.com" en remplaçant "votre_email@example.com" par votre adresse e-mail associée à votre compte GitHub.
Appuyez sur Entrée pour utiliser le chemin d'accès par défaut pour la clé (généralement ~/.ssh/id_ed25519) ou spécifiez un nouveau chemin d'accès si vous le souhaitez.
Entrez une phrase de passe pour sécuriser votre clé SSH. Cette phrase de passe sera requise chaque fois que vous utiliserez votre clé SSH.
La clé publique se trouvera dans le fichier id_ed25519.pub dans le répertoire ~/.ssh.
Ajout de la clé SSH à votre compte GitHub

Copiez la clé publique id_ed25519.pub à partir du terminal ou ouvrez le fichier avec votre éditeur de texte préféré et copiez le contenu.
Connectez-vous à votre compte GitHub.
Cliquez sur votre photo de profil en haut à droite de la page, puis cliquez sur "Settings".
Dans le menu de gauche, cliquez sur "SSH and GPG keys".
Cliquez sur "New SSH key" ou sur "Add SSH key".
Donnez un titre à la clé, par exemple "Mon ordinateur personnel".
Collez la clé publique dans le champ "Key".
Cliquez sur "Add SSH key".
Création de la clé SSH pour votre serveur

Connectez-vous à votre serveur en utilisant votre nom d'utilisateur et votre mot de passe.
Tapez la commande suivante : ssh-keygen -t rsa -b 4096 -C "votre_email@example.com" en remplaçant "votre_email@example.com" par une adresse e-mail à laquelle vous pouvez être contacté.
Appuyez sur Entrée pour utiliser le chemin d'accès par défaut pour la clé (généralement ~/.ssh/id_rsa) ou spécifiez un nouveau chemin d'accès si vous le souhaitez.
Entrez une phrase de passe pour sécuriser votre clé SSH. Cette phrase de passe sera requise chaque fois que vous utiliserez votre clé SSH.
La clé publique se trouvera dans le fichier id_rsa.pub dans le répertoire ~/.ssh.
Ajout de la clé SSH à votre serveur

Connectez-vous à votre serveur en utilisant votre nom d'utilisateur et votre mot de passe.
Tapez la commande suivante : ssh-copy-id username@hostname en remplaçant "username" par votre nom d'utilisateur et "hostname" par le nom ou l'adresse IP de votre serveur.
Entrez votre mot de passe pour l'utilisateur sur votre serveur.
La clé publique est maintenant ajoutée à votre serveur et vous pouvez vous connecter sans avoir à entrer votre mot de passe à chaque fois.
Une fois que vous avez créé et ajouté vos clés SSH à GitHub et à votre serveur, vous pouvez utiliser le workflow GitHub et l'action SSH pour déployer votre projet sur votre serveur. N