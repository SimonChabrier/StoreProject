Comme j'utilise le composant Messenger de Symfony qui travaille en async, il faut lancer un worker pour envoyer les mails.
Voir messenger.yaml pour la configuration.

# activer l'envoi de mail
```bash
bin/console messenger:consume async -vv
```

# désactiver l'envoi de mail
```bash
bin/console messenger:stop-workers
```

# pour le reset-password (voir https://symfony.com/doc/current/security/reset_password.html)
- dans routing.yaml, ajouter
```yaml
default_uri: 'https://127.0.0.1:8000'
```
pour que le lien de reset-password soit correct et ne contienne pas localhost si le serveur est lancé en local sur  https://127.0.0.1:8000/
