<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import { Accordion } from '@hyvor/design/marketing';
</script>

# API Console

L'API Console vous permet d'automatiser les tâches liées à votre newsletter via HTTP, avec une authentification par clé API. C'est la même API que nous utilisons en interne dans la Console.

<h2 id="getting-started">Premiers pas</h2>

- Créez une clé API Console dans **Console &rarr; Paramètres &rarr; Clés API**. Chaque clé doit recevoir un ou plusieurs [scopes](#api-keys), qui limitent ce à quoi elle peut accéder.
- L'URL de base : `https://post.hyvor.com/api/console`
- Pour chaque requête, définissez l'en-tête `Authorization` sur `Bearer <API_KEY>`.
- Méthodes HTTP disponibles :
    - `GET` - Récupérer une ressource
    - `POST` - Créer une ressource ou effectuer une action
    - `PUT` - Mettre à jour une ressource
    - `DELETE` - Supprimer une ressource
- Les paramètres de requête peuvent être envoyés en `JSON` (recommandé) ou en `application/x-www-form-urlencoded`.
- Tous les endpoints renvoient des données JSON. La réponse est un objet ou un tableau d'objets.

<Callout type="info">
	<p>
		Dans cette documentation, tous les objets, paramètres de requête et réponses sont écrits sous
		forme d'interfaces <a href="https://www.typescriptlang.org/" rel="nofollow">TypeScript</a>
		afin de rendre les déclarations de types concises.
	</p>
</Callout>

<h2 id="categories">Catégories</h2>

Les endpoints de l'API Console sont classés selon la ressource avec laquelle ils interagissent.

Aller à une catégorie :

- [Newsletter](#newsletter)
- [Numéro](#issue)
- [Listes](#lists)
- [Abonné](#subscriber)
- [Métadonnées d'abonné](#subscriber-metadata)
- [Profil d'envoi](#sending-profile)
- [Modèle](#template)
- [Utilisateur](#user)
- [Clés API](#api-keys)
- [Médias](#media)
- [Importations](#imports)
- [Exportation](#export)

<!-- ############################## CATEGORIES ################################# -->

<h3 id="newsletter">Newsletter</h3>

Endpoints :

- [`GET /newsletter`](#get-newsletter) - Récupérer les données de la newsletter
- [`PATCH /newsletter`](#update-newsletter) - Mettre à jour une newsletter
- [`POST /newsletter/confirmation-email/preview`](#preview-confirmation-email) - Prévisualiser l'e-mail de confirmation
- [`DELETE /newsletter`](#delete-newsletter) - Supprimer une newsletter

Objets :

- [Objet Newsletter](#newsletter-object)

<h4 id="get-newsletter">Récupérer les données de la newsletter</h4>

`GET /newsletter`

```ts
type Request = {}
type Response = Newsletter
```

<h4 id="update-newsletter">Mettre à jour une newsletter</h4>

`PATCH /newsletter`

```ts
type Request = Partial<Newsletter>  // except id, created_at
type Response = Newsletter
```

<h4 id="preview-confirmation-email">Prévisualiser l'e-mail de confirmation</h4>

`POST /newsletter/confirmation-email/preview`

```ts
type Request = {
    subject?: string | null; // null = sujet actuel
    content?: string | null; // JSON ProseMirror. null = contenu actuel
}
type Response = {
    subject: string;
    html: string;
}
```

<h4 id="delete-newsletter">Supprimer une newsletter</h4>

`DELETE /newsletter`

```ts
type Request = {}
type Response = {}
```

<Callout type="warning">
	Cet endpoint effectue une suppression logique de la newsletter et programme sa suppression définitive après 30 jours.
</Callout>

<h3 id="issue">Numéro</h3>

Endpoints :

- [`GET /issues`](#get-issues) - Récupérer les numéros
- [`POST /issues`](#create-issue) - Créer un numéro
- [`GET /issues/{id}`](#get-issue) - Récupérer un numéro
- [`PATCH /issues/{id}`](#update-issue) - Mettre à jour un numéro
- [`DELETE /issues/{id}`](#delete-issue) - Supprimer un numéro
- [`POST /issues/{id}/send`](#send-issue) - Envoyer un numéro
- [`GET /issues/{id}/preview`](#preview-issue) - Prévisualiser un numéro
- [`GET /issues/{id}/progress`](#get-issue-progress) - Récupérer la progression de l'envoi d'un numéro
- [`GET /issues/{id}/sends`](#get-issue-sends) - Récupérer les envois d'un numéro
- [`GET /issues/{id}/report`](#get-issue-report) - Récupérer le rapport d'un numéro

Objets :

- [Objet Issue](#issue-object)
- [Objet Send](#send-object)

<h4 id="get-issues">Récupérer les numéros</h4>

`GET /issues`

```ts
type Request = {
    limit?: number; // default: 50
    offset?: number; // default: 0
}
type Response = Issue[]
```

<h4 id="create-issue">Créer un numéro</h4>

`POST /issues`

```ts
type Request = {}
type Response = Issue
```

<h4 id="get-issue">Récupérer un numéro</h4>

`GET /issues/{id}`

```ts
type Request = {}
type Response = Issue
```

<h4 id="update-issue">Mettre à jour un numéro</h4>

`PATCH /issues/{id}`

```ts
type Request = {
    subject?: string;
    lists?: number[];
    content?: string;
    sending_profile_id?: number;
}
type Response = Issue
```

<h4 id="delete-issue">Supprimer un numéro</h4>

`DELETE /issues/{id}`

```ts
type Request = {}
type Response = {}
```

<h4 id="send-issue">Envoyer un numéro</h4>

`POST /issues/{id}/send`

```ts
type Request = {}
type Response = Issue
```

<h4 id="preview-issue">Prévisualiser un numéro</h4>

Génère l'aperçu HTML d'un numéro et renvoie le nombre d'abonnés auxquels il pourrait être envoyé.

`GET /issues/{id}/preview`

```ts
type Request = {}
type Response = {
    html: string;
    sendable_subscribers_count: number;
}
```

<h4 id="get-issue-progress">Récupérer la progression de l'envoi d'un numéro</h4>

Récupère la progression de l'envoi d'un numéro en cours d'envoi.

`GET /issues/{id}/progress`

```ts
type Request = {}
type Response = {
    total: number;
    sent: number;
    progress: number; // percentage, 0-100
} | null // null if the issue has no sends yet
```

<h4 id="get-issue-sends">Récupérer les envois d'un numéro</h4>

`GET /issues/{id}/sends`

```ts
type Request = {
    limit?: number; // default: 50
    offset?: number; // default: 0
    search?: string;
    type?: string;
}
type Response = Send[]
```

<h4 id="get-issue-report">Récupérer le rapport d'un numéro</h4>

Récupère le nombre de distributions, d'ouvertures, de clics, de rebonds et de plaintes d'un numéro.

`GET /issues/{id}/report`

```ts
type Request = {}
type Response = {
    counts: {
        total: number;
        pending: number;
        sent: number;
        failed: number;
        unsubscribed: number;
        bounced: number;
        complained: number;
    }
}
```

<h3 id="lists">Listes</h3>

Endpoints :

- [`POST /lists`](#create-list) - Créer une liste
- [`PATCH /lists/{id}`](#update-list) - Mettre à jour une liste
- [`DELETE /lists/{id}`](#delete-list) - Supprimer une liste

Objets :

- [Objet List](#list-object)

<h4 id="create-list">Créer une liste</h4>

`POST /lists`

```ts
type Request = {
    name: string;   // max length: 255
    description?: string;
}
type Response = List
```

<h4 id="update-list">Mettre à jour une liste</h4>

`PATCH /lists/{id}`

```ts
type Request = {
    name?: string;   // max length: 255
    description?: string;
}
type Response = List
```

<h4 id="delete-list">Supprimer une liste</h4>

`DELETE /lists/{id}`

```ts
type Request = {}
type Response = {}
```

<h3 id="subscriber">Abonné</h3>

Endpoints :

- [`GET /subscribers`](#get-subscribers) - Récupérer les abonnés
- [`GET /subscribers/email/{email}`](#get-subscriber-by-email) - Récupérer un abonné par e-mail
- [`POST /subscribers`](#create-update-subscriber) - Créer ou mettre à jour un abonné
- [`POST /subscribers/{id}/resend-opt-in`](#resend-opt-in) - Renvoyer l'e-mail de confirmation d'inscription
- [`DELETE /subscribers/{id}`](#delete-subscriber) - Supprimer un abonné
- [`POST /subscribers/bulk`](#bulk-update-subscriber) - Mettre à jour des abonnés en masse

Objets :

- [Objet Subscriber](#subscriber-object)

<h4 id="get-subscribers">Récupérer les abonnés</h4>

`GET /subscribers`

```ts
type Request = {
    limit?: number; // default: 50
    offset?: number; // default: 0

    // filter by status
    status?: 'subscribed' | 'unsubscribed' | 'pending';

    // filter by list
    list_id?: number;

    // search by email
    search?: string;
}
type Response = Subscriber[]
```

<h4 id="get-subscriber-by-email">Récupérer un abonné par e-mail</h4>

`GET /subscribers/email/{email}`

```ts
type Request = {}
type Response = Subscriber // 404 if not found
```

<h4 id="create-update-subscriber">Créer ou mettre à jour un abonné</h4>

`POST /subscribers`

```ts
type Request = {
    // If a subscriber with the given email already exists, it will be updated.
    // Otherwise, a new subscriber will be created.
    email: string;

    // Subscribe to or unsubscribe from lists based
    // on the given \`lists_strategy\`.
    // an array of list IDs or names.
    lists?: (number | string)[];

    // The subscriber's subscription status
    // default: subscribed
    status?: 'subscribed' | 'pending';

    // the source of the subscriber
    // default: console
    source?: 'console' | 'form' | 'import';

    // subscriber's IP address
    subscribe_ip?: string | null;

    // unix timestamp of when the subscriber opted in
    // if not set, it will be set to the current time if status is 'subscribed'
    subscribed_at?: number | null; // unix timestamp

    // additional metadata for the subscriber
    // keys must be defined in the Subscriber Metadata Definitions section (or using the API)
    metadata?: Record<string, string>;

    // ============ SETTINGS ===========
    // change how the endpoint behaves

    // how \`lists\` field is processed when updating an existing subscriber's list subscriptions.
    // merge: merges the lists (default)
    // overwrite: overwrites the lists
    // remove: removes from the current lists
    lists_strategy?: 'merge' | 'overwrite' | 'remove';

    // if the subscriber was previously removed from a list,
    // define the reason(s) for skipping the re-subscription to that list.
    // see below for more info
    // default: ['unsubscribe', 'bounce', 'complaint']
    list_skip_resubscribe_on?: ('unsubscribe' | 'bounce' | 'complaint' | 'other')[];

    // define the reason for removing the subscriber from a list
    // (only when updating, see below for more info)
    // default: 'unsubscribe'
    list_removal_reason?: 'unsubscribe' | 'bounce' | 'complaint' | 'other';

    // whether to overwrite or merge the subscriber's metadata
    // when updating an existing subscriber.
    // default: 'merge'
    metadata_strategy?: 'merge' | 'overwrite';

    // whether to send a confirmation email when adding a subscriber with 'pending' status
    // or when changing an existing subscriber's status to 'pending'.
    // default: false
    send_pending_confirmation_email?: boolean;
}
type Response = Subscriber
```

<h5 id="managing-list-subscriptions">Gérer les désabonnements et réabonnements aux listes</h5>

Pour chaque abonné, Hyvor Post enregistre les listes dont il s'est précédemment désabonné. Cela facilite la création d'automatisations autour des abonnements aux listes, tout en respectant les préférences des abonnés.

`list_skip_resubscribe_on` : lors de l'ajout d'un abonné existant à une liste dont il a été retiré auparavant, ce paramètre détermine lesquels des motifs de retrait ci-dessous doivent bloquer le réajout. Par défaut, les désabonnements, rebonds et plaintes précédents bloquent tous le réajout ; passez un tableau vide pour toujours réajouter l'abonné, quelle que soit la raison de son départ.

`list_removal_reason` :

- `unsubscribe` - utilisez ce motif si l'abonné demande explicitement à être retiré de la liste (par exemple, il a décoché une case pour se désabonner). Un désabonnement est alors enregistré, ce qui bloque les réajouts futurs, sauf si le paramètre `list_skip_resubscribe_on` de la demande de réajout exclut `unsubscribe`. Le formulaire de désabonnement par défaut de Hyvor Post utilise ce motif.
- `bounce` - enregistré automatiquement lorsqu'un envoi à l'abonné provoque un rebond définitif.
- `complaint` - enregistré automatiquement lorsque l'abonné signale un envoi comme spam.
- `other` - utilisez ce motif si vous souhaitez retirer l'abonné de la liste sans l'enregistrer sous l'un des motifs ci-dessus (ne bloque pas les réajouts futurs par défaut).

<h5 id="subscriber-examples">Exemples</h5>

<div style="display: flex; flex-direction: column; gap: 10px">
	<Accordion title="Créer ou mettre à jour un abonné">
		<div>
			Cet exemple crée un nouvel abonné inscrit à la liste « Default ». Si un abonné avec la même
			adresse e-mail existe déjà, il est mis à jour et ses listes sont remplacées par « Default »
			uniquement (les listes existantes sont écrasées).
		</div>

		

```json
{
    "email": "example@example.com",
    "lists": ["Default"]
}
```

	</Accordion>

	<Accordion title="Ajouter un abonné à une liste sans modifier ses autres listes">
		<div>
			En supposant que vous ayez une liste avec l'ID 123, cet exemple ajoute l'abonné à cette liste
			sans modifier ses autres abonnements. Si l'abonné est déjà inscrit à la liste, rien n'est
			modifié.
		</div>

		

```json
{
    "email": "example@example.com",
    "lists": [123],
    "lists_strategy": "add"
}
```

	</Accordion>

	<Accordion title="Retirer un abonné d'une liste">
		<div>Cet exemple retire simplement l'abonné de la liste nommée « Paid Users ».</div>

		

```json
{
    "email": "example@example.com",
    "lists": ["Paid Users"],
    "lists_strategy": "remove",

    // unsubscribe, bounce, or other
    "list_removal_reason": "unsubscribe"
}
```

	</Accordion>

	<Accordion title="Ajouter un abonné en attente et envoyer un e-mail de confirmation">
		<div>
			Cet exemple crée un abonné ou met à jour un abonné existant avec le statut « pending », puis
			lui envoie un e-mail lui demandant de confirmer son abonnement.
		</div>
		

```json
{
    "email": "example@example.com",
    "lists": ["Default"],
    "status": "pending",
    "send_pending_confirmation_email": true
}
```

	</Accordion>

	<Accordion title="Réabonner un abonné qui s'était désabonné d'une liste">
		<div>
			Par défaut, cet endpoint ignore les tentatives de réabonnement aux listes dont l'abonné s'est
			désabonné (ou dont il a été retiré suite à un rebond). Cet exemple montre comment modifier ce
			comportement.
		</div>
		

```json
{
    "email": "example@example.com",
    "lists": ["Default"],
    "lists_strategy": "add",
    // ignore unsubscription if the subscriber was removed from the list due to a bounce
    // but allow re-adding if they previously unsubscribed themselves
    "list_skip_resubscribe_on": ["bounce"]
}
```

		<p>
			Pour forcer le réajout malgré les désabonnements et les rebonds précédents, utilisez un tableau
			vide pour <code>list_skip_resubscribe_on</code>.
		</p>
	</Accordion>
</div>

<h4 id="resend-opt-in">Renvoyer l'e-mail de confirmation d'inscription</h4>

Renvoie l'e-mail de confirmation d'inscription à un abonné en attente.

`POST /subscribers/{id}/resend-opt-in`

```ts
type Request = {}
type Response = {}
```

<h4 id="delete-subscriber">Supprimer un abonné</h4>

`DELETE /subscribers/{id}`

```ts
type Request = {}
type Response = {}
```

<h4 id="bulk-update-subscriber">Mettre à jour des abonnés en masse</h4>

`POST /subscribers/bulk`

```ts
type Request = {
    subscribers_ids: number[];
    action: 'delete' | 'status_change' | 'metadata_update';
    status?: 'subscribed' | 'unsubscribed' | 'pending'; // required if action is status_change
    metadata?: Record<string, string>; // required if action is metadata_update
}
type Response = {
    status: string;
    message: string;
    subscribers: Subscriber[];
}
```

<h3 id="subscriber-metadata">Métadonnées d'abonné</h3>

Les définitions de métadonnées d'abonné vous permettent de définir des champs personnalisés pour les abonnés. Ces champs servent à stocker des informations supplémentaires sur les abonnés.

Endpoints :

- [`POST /subscriber-metadata-definitions`](#create-subscriber-metadata-definition) - Créer une définition de métadonnée d'abonné
- [`PATCH /subscriber-metadata-definitions/{id}`](#update-subscriber-metadata-definition) - Mettre à jour une définition de métadonnée d'abonné
- [`DELETE /subscriber-metadata-definitions/{id}`](#delete-subscriber-metadata-definition) - Supprimer une définition de métadonnée d'abonné

Objets :

- [Objet SubscriberMetadataDefinition](#subscriber-metadata-definition-object)

<h4 id="create-subscriber-metadata-definition">Créer une définition de métadonnée d'abonné</h4>

`POST /subscriber-metadata-definitions`

```ts
type Request = {
    key: string;   // max length: 255
    name: string;  // max length: 255
}
type Response = SubscriberMetadataDefinition
```

<Callout type="info">
	<ul>
		<li><code>key</code> ne peut contenir que des lettres minuscules, des chiffres et des tirets bas.</li>
		<li>Une fois créée, la <code>key</code> ne peut plus être modifiée.</li>
	</ul>
</Callout>

<h4 id="update-subscriber-metadata-definition">Mettre à jour une définition de métadonnée d'abonné</h4>

`PATCH /subscriber-metadata-definitions/{id}`

```ts
type Request = {
    name: string;  // max length: 255
}
type Response = SubscriberMetadataDefinition
```

<h4 id="delete-subscriber-metadata-definition">Supprimer une définition de métadonnée d'abonné</h4>

`DELETE /subscriber-metadata-definitions/{id}`

```ts
type Request = {}
type Response = {}
```

<h3 id="sending-profile">Profil d'envoi</h3>

Endpoints :

- [`GET /sending-profiles`](#get-sending-profiles) - Récupérer les profils d'envoi
- [`POST /sending-profiles`](#create-sending-profile) - Créer un profil d'envoi
- [`PATCH /sending-profiles/{id}`](#update-sending-profile) - Mettre à jour un profil d'envoi
- [`DELETE /sending-profiles/{id}`](#delete-sending-profile) - Supprimer un profil d'envoi

Objets :

- [Objet SendingProfile](#sending-profile-object)

<h4 id="get-sending-profiles">Récupérer les profils d'envoi</h4>

`GET /sending-profiles`

```ts
type Request = {}
type Response = SendingProfile[]
```

<h4 id="create-sending-profile">Créer un profil d'envoi</h4>

`POST /sending-profiles`

```ts
type Request = {
    from_email: string;
    from_name?: string | null;
    reply_to_email?: string | null;
    brand_name?: string | null;
    brand_logo?: string | null; // a publicly accessible URL of the logo
    brand_url?: string | null;
}
type Response = SendingProfile
```

<h4 id="update-sending-profile">Mettre à jour un profil d'envoi</h4>

`PATCH /sending-profiles/{id}`

```ts
type Request = {
    from_email?: string;
    from_name?: string | null;
    reply_to_email?: string | null;
    brand_name?: string | null;
    brand_logo?: string | null; // a publicly accessible URL of the logo
    brand_url?: string | null;
    is_default?: boolean;
}
type Response = SendingProfile
```

<h4 id="delete-sending-profile">Supprimer un profil d'envoi</h4>

Le profil d'envoi système ne peut pas être supprimé.

`DELETE /sending-profiles/{id}`

```ts
type Request = {}
type Response = SendingProfile[] // the newsletter's remaining sending profiles
```

<h3 id="template">Modèle</h3>

Hyvor Post propose un système de modèles de newsletter flexible qui vous permet de personnaliser
l'apparence de vos newsletters.

Endpoints :

- [`GET /templates`](#get-template) - Récupérer le modèle de la newsletter
- [`PATCH /templates`](#update-template) - Mettre à jour le modèle de la newsletter
- [`POST /templates/render`](#render-template) - Générer le modèle de la newsletter avec du contenu

Objets :

- [Objet Template](#template-object)

<h4 id="get-template">Récupérer le modèle de la newsletter</h4>

`GET /templates`

```ts
type Request = {}
type Response = Template
```

<h4 id="update-template">Mettre à jour le modèle de la newsletter</h4>

`PATCH /templates`

```ts
type Request = {
    template?: string;
}
type Response = Template
```

<h4 id="render-template">Générer le modèle de la newsletter avec du contenu</h4>

`POST /templates/render`

```ts
type Request = {
    template?: string | null;
}
type Response = {
    html: string;
}
```

<h3 id="user">Utilisateur</h3>

Les administrateurs de l'organisation propriétaire de la newsletter peuvent être ajoutés comme utilisateurs pour collaborer à sa gestion.

Endpoints :

- [`GET /users`](#get-user) - Récupérer les utilisateurs
- [`POST /users`](#create-user) - Créer un utilisateur
- [`DELETE /users`](#delete-user) - Supprimer un utilisateur

Objets :

- [Objet User](#user-object)

<h4 id="get-user">Récupérer les utilisateurs</h4>

`GET /users`

```ts
type Request = {}
type Response = User[]
```

<h4 id="create-user">Créer un utilisateur</h4>

`POST /users`

L'utilisateur doit déjà être membre de l'organisation propriétaire de cette newsletter.

```ts
type Request = {
    user_id: number; // the user's id in HYVOR (AuthInterface)

    // what to do if the user is already added to the newsletter
    // throw: return a 400 error (default)
    // ignore: return the existing user without an error
    on_duplicate?: 'throw' | 'ignore';
}
type Response = User
```

<Callout type="info">
	<ul>
		<li>
			Renvoie une erreur 400 si l'utilisateur n'est pas membre de l'organisation propriétaire de cette newsletter.
		</li>
	</ul>
</Callout>

<h4 id="delete-user">Supprimer un utilisateur</h4>

`DELETE /users`

```ts
type Request = {
    // one of user_id or id is required
    user_id?: number; // the user's id in HYVOR (AuthInterface)
    id?: number; // the user's id in this newsletter's user list
}
type Response = {}
```

<h3 id="api-keys">Clés API</h3>

Chaque clé API reçoit un ou plusieurs scopes, qui limitent les ressources et les actions auxquelles elle peut accéder. Scopes disponibles :

- `newsletter.read` / `newsletter.write` / `newsletter.delete`
- `issues.read` / `issues.write`
- `sending_profiles.read` / `sending_profiles.write`
- `subscribers.read` / `subscribers.write`
- `users.read` / `users.write`
- `templates.read` / `templates.write`
- `api_keys.read` / `api_keys.write`
- `media.write`
- `data.read` / `data.write` - listes d'abonnés, importations et exportations

Endpoints :

- [`GET /api-keys`](#get-api-keys) - Récupérer les clés API
- [`POST /api-keys`](#create-api-key) - Créer une clé API
- [`PATCH /api-keys/{id}`](#update-api-key) - Mettre à jour une clé API
- [`POST /api-keys/{id}`](#regenerate-api-key) - Régénérer une clé API
- [`DELETE /api-keys/{id}`](#delete-api-key) - Supprimer une clé API

Objets :

- [Objet ApiKey](#api-key-object)

<h4 id="get-api-keys">Récupérer les clés API</h4>

La clé brute n'est pas renvoyée ; seules ses métadonnées le sont.

`GET /api-keys`

```ts
type Request = {}
type Response = ApiKey[]
```

<h4 id="create-api-key">Créer une clé API</h4>

`POST /api-keys`

```ts
type Request = {
    name: string;   // max length: 255
    scopes: string[]; // see the list of scopes above
}
type Response = ApiKey
```

<Callout type="warning">
	La propriété <code>key</code> (la clé brute) n'est renvoyée qu'une seule fois, à la création.
	Conservez-la en lieu sûr : elle ne pourra plus être récupérée.
</Callout>

<h4 id="update-api-key">Mettre à jour une clé API</h4>

`PATCH /api-keys/{id}`

```ts
type Request = {
    name?: string;   // max length: 255
    is_enabled?: boolean;
    scopes?: string[];
}
type Response = ApiKey
```

<h4 id="regenerate-api-key">Régénérer une clé API</h4>

Régénère la clé brute d'une clé API. L'ancienne clé est invalidée immédiatement et la nouvelle clé brute n'est renvoyée qu'une seule fois.

`POST /api-keys/{id}`

```ts
type Request = {}
type Response = ApiKey
```

<h4 id="delete-api-key">Supprimer une clé API</h4>

Les requêtes effectuées avec la clé supprimée seront rejetées immédiatement.

`DELETE /api-keys/{id}`

```ts
type Request = {}
type Response = {}
```

<h3 id="media">Médias</h3>

Endpoints :

- [`POST /media`](#upload-media) - Téléverser un média

Objets :

- [Objet Media](#media-object)

<h4 id="upload-media">Téléverser un média</h4>

`POST /media`

```ts
type Request = {
    // max size: 10MB
    // supported formats: jpg, jpeg, png, gif, webp
    file: File;
    folder: 'issue_images' | 'newsletter_images';
}
type Response = Media
```

<h3 id="export">Exportation</h3>

Endpoints :

- [`GET /export`](#get-exports) - Récupérer les exportations d'abonnés
- [`POST /export`](#create-export) - Créer une exportation d'abonnés

Objets :

- [Objet SubscriberExport](#subscriber-export-object)

<h4 id="get-exports">Récupérer les exportations d'abonnés</h4>

`GET /export`

```ts
type Request = {}
type Response = SubscriberExport[]
```

<h4 id="create-export">Créer une exportation d'abonnés</h4>

`POST /export`

```ts
type Request = {}
type Response = SubscriberExport
```

<!-- ############################### OBJECTS ################################## -->

<h2 id="objects">Objets</h2>

<h3 id="newsletter-object">Objet Newsletter</h3>

```ts
interface Newsletter {
    id: string;
    subdomain: string;
    created_at: number; // unix timestamp
    name: string;
    language_code: string | null;
    is_rtl: boolean;
    metadata: Record<string, string>;

    address: string | null;
    unsubscribe_text: string | null;
    branding: boolean;

    template_color_accent: string | null;
    template_color_accent_text: string | null;
    template_color_background: string | null;
    template_color_background_text: string | null;
    template_color_box: string | null;
    template_color_box_text: string | null;

    template_box_shadow: string | null;
    template_box_radius: string | null;
    template_box_border: string | null;

    template_font_family: string | null;
    template_font_size: string | null;
    template_font_weight: string | null;
    template_font_weight_heading: string | null;
    template_font_line_height: string | null;

    form_title: string | null;
    form_description: string | null;
    form_footer_text: string | null;
    form_button_text: string | null;
    form_success_message: string | null;

    form_width: number | null;  // null = 100%
    form_custom_css: string | null;

    form_color_light_text: string | null; // null = inherit
    form_color_light_text_light: string | null;
    form_color_light_accent: string | null;
    form_color_light_accent_text: string | null;
    form_color_light_input: string | null;
    form_color_light_input_text: string | null;
    form_light_input_box_shadow: string | null;
    form_light_input_border: string | null;
    form_light_border_radius: number | null;

    form_color_dark_text: string | null; // null = inherit
    form_color_dark_text_light: string | null;
    form_color_dark_accent: string | null;
    form_color_dark_accent_text: string | null;
    form_color_dark_input: string | null;
    form_color_dark_input_text: string | null;
    form_dark_input_box_shadow: string | null;
    form_dark_input_border: string | null;
    form_dark_border_radius: number | null;

    form_default_color_palette: 'light' | 'dark' | 'os';
    form_input_border_radius: number;

    confirmation_email_subject: string | null; // null = par défaut. Accepte {{newsletter_name}}
    confirmation_email_content: string | null; // JSON ProseMirror. null = par défaut. Doit contenir {{confirm_url}}
}
```

<h3 id="issue-object">Objet Issue</h3>

```ts
interface Issue {
    id: number;
    uuid: string;
    created_at: number; // unix timestamp
    subject: string | null;
    content: string | null;
    sending_profile_id: number;
    status: 'draft' | 'scheduled' | 'sending' | 'sent';
    lists: number[];

    scheduled_at: number | null; // unix timestamp
    sending_at: number | null; // unix timestamp
    sent_at: number | null; // unix timestamp
    total_sends: number;

    from_email: string | null;
    from_name: string | null;
    reply_to_email: string | null;

    sendable_subscribers_count: number;
}
```

<h3 id="send-object">Objet Send</h3>

```ts
interface Send {
    id: number;
    created_at: number; // unix timestamp
    subscriber: Subscriber | null;
    email: string;
    status: 'pending' | 'sent' | 'failed';

    sent_at: number | null; // unix timestamp
    failed_at: number | null; // unix timestamp
    delivered_at: number | null; // unix timestamp
    unsubscribed_at: number | null; // unix timestamp
    bounced_at: number | null; // unix timestamp
    hard_bounce: boolean;
    complained_at: number | null; // unix timestamp
}
```

<h3 id="list-object">Objet List</h3>

```ts
interface List {
    id: number;
    created_at: number; // unix timestamp
    name: string;
    description: string | null;
    subscribers_count: number;
}
```

<h3 id="subscriber-object">Objet Subscriber</h3>

```ts
interface Subscriber {
    id: number;
    email: string;
    source: 'console' | 'form' | 'import';
    status: 'subscribed' | 'pending';
    list_ids: number[];
    lists: string[]; // list names
    subscribe_ip: string | null;
    subscribed_at: number | null; // unix timestamp
    metadata: Record<string, string>;
}
```

<h3 id="subscriber-metadata-definition-object">Objet SubscriberMetadataDefinition</h3>

```ts
interface SubscriberMetadataDefinition {
    id: number;
    created_at: number; // unix timestamp
    key: string;
    name: string;
    type: 'text'; // only 'text' is currently supported
}
```

<h3 id="sending-profile-object">Objet SendingProfile</h3>

```ts
interface SendingProfile {
    id: number;
    created_at: number; // unix timestamp
    from_email: string;
    from_name: string | null;
    reply_to_email: string | null;
    brand_name: string | null;
    brand_logo: string | null;
    brand_url: string | null;
    is_default: boolean;
    is_system: boolean;
}
```

<h3 id="template-object">Objet Template</h3>

```ts
interface Template {
    template: string;
}
```

<h3 id="user-mini-object">Objet UserMini</h3>

```ts
interface UserMiniObject {
    name: string;
    email: string;
    username: string | null;
    picture_url: string | null;
}
```

<h3 id="user-object">Objet User</h3>

```ts
interface User {
    id: number;
    role: 'owner' | 'admin';
    created_at: number; // unix timestamp
    user: UserMiniObject;
}
```

<h3 id="media-object">Objet Media</h3>

```ts
interface Media {
    id: number;
    created_at: number; // unix timestamp
    folder: 'issue_images' | 'newsletter_images' | 'import' | 'export';
    url: string;
    size: number; // in bytes
    extension: string;
}
```

<h3 id="subscriber-export-object">Objet SubscriberExport</h3>

```ts
interface SubscriberExport {
    id: number;
    created_at: number; // unix timestamp
    status: 'pending' | 'completed' | 'failed';
    error_message: string | null;
    url: string | null;
}
```
