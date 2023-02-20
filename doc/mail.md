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