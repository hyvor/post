# Hébergement : premiers pas

<h2 id="relay">Configuration de Relay</h2>

Hyvor Post envoie les e-mails avec Hyvor Relay. Vous pouvez utiliser Hyvor Relay Cloud ou une instance auto-hébergée.

- Créez un nouveau projet dans Hyvor Relay. Idéalement, choisissez le type « distributional » si cette file d'attente est activée sur votre instance.
- Créez une clé API avec les scopes suivants :
    - `sends.send`
    - `domains.read`
    - `domains.write`
- Définissez les variables d'environnement suivantes dans Hyvor Post :
    - `RELAY_URL` : URL de l'instance Relay. Pour le cloud, utilisez `https://relay.hyvor.com`
    - `RELAY_API_KEY` : la clé API créée ci-dessus.
- Ensuite, définissez `SYSTEM_MAIL_DOMAIN` sur le domaine depuis lequel vous enverrez les e-mails système. Les e-mails de notification système (vérification de domaine, etc.) sont envoyés depuis `notifications@<SYSTEM_MAIL_DOMAIN>` et chaque newsletter obtient sa propre adresse e-mail, comme `<newsletter-subdomain>@<SYSTEM_MAIL_DOMAIN>`.

    Notez que les utilisateurs de Hyvor Post **ne peuvent pas configurer** ce domaine exact comme domaine personnalisé. Vous pouvez également définir la variable d'environnement `SYSTEM_MAIL_REPLY_TO` pour indiquer l'adresse de réponse des e-mails système.
