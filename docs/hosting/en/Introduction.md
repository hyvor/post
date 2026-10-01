# Hosting: Get Started

<h2 id="relay">Relay Setup</h2>

Hyvor Post sends email using Hyvor Relay. You can use Hyvor Relay Cloud or a self-hosted instance.

- Create a new project in Hyvor Relay. Ideally, select the type "distributional" if that queue is enabled on your instance.
- Create an API key with the following scopes:
    - `sends.send`
    - `domains.read`
    - `domains.write`
- Set the following environment variables in Hyvor Post:
    - `RELAY_URL`: URL of the Relay instance. For cloud, use `https://relay.hyvor.com`
    - `RELAY_API_KEY`: The API key created above.
- Then, set `SYSTEM_MAIL_DOMAIN` to the domain you will be sending system emails from. System notification emails (domain verification, etc.) are sent from `notifications@<SYSTEM_MAIL_DOMAIN>` and each newsletter will get its own email address like `<newsletter-subdomain>@<SYSTEM_MAIL_DOMAIN>`.

    Note that users of Hyvor Post **cannot set up** this exact domain as custom domain. Optionally, you can also set `SYSTEM_MAIL_REPLY_TO` env variable to set the reply-to address for system emails.
