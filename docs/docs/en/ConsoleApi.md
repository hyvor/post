<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import { Accordion } from '@hyvor/design/marketing';
</script>

# Console API

The Console API allows you to automate your newsletter-related tasks over HTTP with API key authentication. This is the same API that we internally use at the Console.

<h2 id="getting-started">Getting Started</h2>

- Create a Console API key at **Console &rarr; Settings &rarr; API Keys**. Each key must be granted one or more [scopes](#api-keys), which limit what it can access.
- The base URL: `https://post.hyvor.com/api/console`
- For each request, set `Authorization` header to `Bearer <API_KEY>`.
- Available HTTP methods:
    - `GET` - Retrieve a resource
    - `POST` - Create a resource or perform an action
    - `PUT` - Update a resource
    - `DELETE` - Remove a resource
- Request params can be set as `JSON` (recommended) or as `application/x-www-form-urlencoded`.
- All endpoints return JSON data. The response will be an object or an array of objects.

<Callout type="info">
	<p>
		In this documentation, all objects, request params, and responses are written as <a
			href="https://www.typescriptlang.org/"
			rel="nofollow">Typescript</a
		> interfaces in order to make type declarations concise.
	</p>
</Callout>

<h2 id="categories">Categories</h2>

The Console API endpoints are categorized based on the resource they interact with.

Jump to each category:

- [Newsletter](#newsletter)
- [Issue](#issue)
- [Lists](#lists)
- [Subscriber](#subscriber)
- [Subscriber Metadata](#subscriber-metadata)
- [Sending Profile](#sending-profile)
- [Template](#template)
- [User](#user)
- [API Keys](#api-keys)
- [Media](#media)
- [Imports](#imports)
- [Export](#export)

<!-- ############################## CATEGORIES ################################# -->

<h3 id="newsletter">Newsletter</h3>

Endpoints:

- [`GET /newsletter`](#get-newsletter) - Get newsletter data
- [`PATCH /newsletter`](#update-newsletter) - Update a newsletter
- [`DELETE /newsletter`](#delete-newsletter) - Delete a newsletter

Objects:

- [Newsletter Object](#newsletter-object)

<h4 id="get-newsletter">Get newsletter data</h4>

`GET /newsletter`

```ts
type Request = {}
type Response = Newsletter
```

<h4 id="update-newsletter">Update a newsletter</h4>

`PATCH /newsletter`

```ts
type Request = Partial<Newsletter>  // except id, created_at
type Response = Newsletter
```

<h4 id="delete-newsletter">Delete a newsletter</h4>

`DELETE /newsletter`

```ts
type Request = {}
type Response = {}
```

<Callout type="warning">
	This endpoint will soft-delete the newsletter, scheduling it for permanent deletion after 30 days.
</Callout>

<h3 id="issue">Issue</h3>

Endpoints:

- [`GET /issues`](#get-issues) - Get issues
- [`POST /issues`](#create-issue) - Create an issue
- [`GET /issues/{id}`](#get-issue) - Get an issue
- [`PATCH /issues/{id}`](#update-issue) - Update an issue
- [`DELETE /issues/{id}`](#delete-issue) - Delete an issue
- [`POST /issues/{id}/send`](#send-issue) - Send an issue
- [`GET /issues/{id}/preview`](#preview-issue) - Preview an issue
- [`GET /issues/{id}/progress`](#get-issue-progress) - Get issue sending progress
- [`GET /issues/{id}/sends`](#get-issue-sends) - Get issue sends
- [`GET /issues/{id}/report`](#get-issue-report) - Get issue report

Objects:

- [Issue Object](#issue-object)
- [Send Object](#send-object)

<h4 id="get-issues">Get issues</h4>

`GET /issues`

```ts
type Request = {
    limit?: number; // default: 50
    offset?: number; // default: 0
}
type Response = Issue[]
```

<h4 id="create-issue">Create an issue</h4>

`POST /issues`

```ts
type Request = {}
type Response = Issue
```

<h4 id="get-issue">Get an issue</h4>

`GET /issues/{id}`

```ts
type Request = {}
type Response = Issue
```

<h4 id="update-issue">Update an issue</h4>

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

<h4 id="delete-issue">Delete an issue</h4>

`DELETE /issues/{id}`

```ts
type Request = {}
type Response = {}
```

<h4 id="send-issue">Send an issue</h4>

`POST /issues/{id}/send`

```ts
type Request = {}
type Response = Issue
```

<h4 id="preview-issue">Preview an issue</h4>

Renders the HTML preview of an issue, and returns the number of subscribers it would be sendable to.

`GET /issues/{id}/preview`

```ts
type Request = {}
type Response = {
    html: string;
    sendable_subscribers_count: number;
}
```

<h4 id="get-issue-progress">Get issue sending progress</h4>

Get the sending progress of an issue that is currently being sent.

`GET /issues/{id}/progress`

```ts
type Request = {}
type Response = {
    total: number;
    sent: number;
    progress: number; // percentage, 0-100
} | null // null if the issue has no sends yet
```

<h4 id="get-issue-sends">Get issue sends</h4>

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

<h4 id="get-issue-report">Get issue report</h4>

Get the delivery, open, click, bounce, and complaint counts of an issue.

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

<h3 id="lists">Lists</h3>

Endpoints:

- [`POST /lists`](#create-list) - Create a list
- [`PATCH /lists/{id}`](#update-list) - Update a list
- [`DELETE /lists/{id}`](#delete-list) - Delete a list

Objects:

- [List Object](#list-object)

<h4 id="create-list">Create a list</h4>

`POST /lists`

```ts
type Request = {
    name: string;   // max length: 255
    description?: string;
}
type Response = List
```

<h4 id="update-list">Update a list</h4>

`PATCH /lists/{id}`

```ts
type Request = {
    name?: string;   // max length: 255
    description?: string;
}
type Response = List
```

<h4 id="delete-list">Delete a list</h4>

`DELETE /lists/{id}`

```ts
type Request = {}
type Response = {}
```

<h3 id="subscriber">Subscriber</h3>

Endpoints:

- [`GET /subscribers`](#get-subscribers) - Get subscribers
- [`GET /subscribers/email/{email}`](#get-subscriber-by-email) - Get a subscriber by email
- [`POST /subscribers`](#create-update-subscriber) - Create or update a subscriber
- [`POST /subscribers/{id}/resend-opt-in`](#resend-opt-in) - Resend opt-in confirmation email
- [`DELETE /subscribers/{id}`](#delete-subscriber) - Delete a subscriber
- [`POST /subscribers/bulk`](#bulk-update-subscriber) - Bulk update subscribers

Objects:

- [Subscriber Object](#subscriber-object)

<h4 id="get-subscribers">Get subscribers</h4>

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

<h4 id="get-subscriber-by-email">Get a subscriber by email</h4>

`GET /subscribers/email/{email}`

```ts
type Request = {}
type Response = Subscriber // 404 if not found
```

<h4 id="create-update-subscriber">Create or update a subscriber</h4>

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

<h5 id="managing-list-subscriptions">Managing list unsubscriptions and re-subscriptions</h5>

For all subscribers, Hyvor Post records the lists they have previously unsubscribed from. This makes it easier to build automations around list subscriptions while respecting subscribers' preferences.

`list_skip_resubscribe_on`: when adding an existing subscriber to a list they were previously removed from, this setting controls which of the removal reasons below should block the re-add. By default, previous unsubscribes, bounces, and complaints all block a re-add; pass an empty array to always re-add regardless of why they left.

`list_removal_reason`:

- `unsubscribe` - use this reason if the subscriber is explicitly asking to be removed from the list (e.g. they unchecked a checkbox to unsubscribe). This will record an unsubscription, blocking future re-adds unless the re-add request's `list_skip_resubscribe_on` excludes `unsubscribe`. Hyvor Post's default unsubscribe form uses this.
- `bounce` - recorded automatically when a send to the subscriber hard-bounces.
- `complaint` - recorded automatically when the subscriber marks a send as spam.
- `other` - use this reason if you want to remove the subscriber from the list without recording it as one of the reasons above (does not block future re-adds by default).

<h5 id="subscriber-examples">Examples</h5>

<div style="display: flex; flex-direction: column; gap: 10px">
	<Accordion title="Creating or updating a subscriber">
		<div>
			This example creates a new subscriber with a subscription to the "Default" list. If a
			subscriber exists in with the same email, they will be updated and their lists will be set to
			only "Default" (overwriting existing lists).
		</div>

		

```json
{
    "email": "example@example.com",
    "lists": ["Default"]
}
```

	</Accordion>

	<Accordion title="Adding a subscriber to a list without affecting their other lists">
		<div>
			Assuming you have a list with List ID 123, this example adds the subscriber to that list
			without affecting their other list subscriptions. If the subscriber is already subscribed to
			the list, no changes will be made.
		</div>

		

```json
{
    "email": "example@example.com",
    "lists": [123],
    "lists_strategy": "add"
}
```

	</Accordion>

	<Accordion title="Removing a subscriber from a list">
		<div>This example simply removes the subscriber from the list named "Paid Users".</div>

		

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

	<Accordion title="Adding a pending subscriber and sending a confirmation email">
		<div>
			This example creates a subscriber or updates an existing subscriber with "pending" status, and
			will send a confirmation email to the subscriber asking them to confirm their subscription.
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

	<Accordion title="Resubscribing a subscriber who previously unsubscribed from a list">
		<div>
			By default, this endpoint ignores re-subscription attempts to lists that the subscriber has
			previously unsubscribed from (or was removed from due to a bounce). This example shows how to
			override that behavior.
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
			To force re-adding both previous unsubscribes and bounces, use an empty array for <code
				>list_skip_resubscribe_on</code
			>.
		</p>
	</Accordion>
</div>

<h4 id="resend-opt-in">Resend opt-in confirmation email</h4>

Resends the opt-in confirmation email to a pending subscriber.

`POST /subscribers/{id}/resend-opt-in`

```ts
type Request = {}
type Response = {}
```

<h4 id="delete-subscriber">Delete a subscriber</h4>

`DELETE /subscribers/{id}`

```ts
type Request = {}
type Response = {}
```

<h4 id="bulk-update-subscriber">Bulk update subscribers</h4>

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

<h3 id="subscriber-metadata">Subscriber Metadata</h3>

Subscriber metadata definitions allow you to define custom fields for subscribers. These fields can be used to store additional information about subscribers.

Endpoints:

- [`POST /subscriber-metadata-definitions`](#create-subscriber-metadata-definition) - Create a subscriber metadata definition
- [`PATCH /subscriber-metadata-definitions/{id}`](#update-subscriber-metadata-definition) - Update a subscriber metadata definition
- [`DELETE /subscriber-metadata-definitions/{id}`](#delete-subscriber-metadata-definition) - Delete a subscriber metadata definition

Objects:

- [Subscriber Metadata Definition Object](#subscriber-metadata-definition-object)

<h4 id="create-subscriber-metadata-definition">Create a subscriber metadata definition</h4>

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
		<li><code>key</code> can only contain lowercase letters, numbers, and underscores.</li>
		<li>Once created, the <code>key</code> cannot be changed.</li>
	</ul>
</Callout>

<h4 id="update-subscriber-metadata-definition">Update a subscriber metadata definition</h4>

`PATCH /subscriber-metadata-definitions/{id}`

```ts
type Request = {
    name: string;  // max length: 255
}
type Response = SubscriberMetadataDefinition
```

<h4 id="delete-subscriber-metadata-definition">Delete a subscriber metadata definition</h4>

`DELETE /subscriber-metadata-definitions/{id}`

```ts
type Request = {}
type Response = {}
```

<h3 id="sending-profile">Sending Profile</h3>

Endpoints:

- [`GET /sending-profiles`](#get-sending-profiles) - Get sending profiles
- [`POST /sending-profiles`](#create-sending-profile) - Create a sending profile
- [`PATCH /sending-profiles/{id}`](#update-sending-profile) - Update a sending profile
- [`DELETE /sending-profiles/{id}`](#delete-sending-profile) - Delete a sending profile

Objects:

- [Sending Profile Object](#sending-profile-object)

<h4 id="get-sending-profiles">Get sending profiles</h4>

`GET /sending-profiles`

```ts
type Request = {}
type Response = SendingProfile[]
```

<h4 id="create-sending-profile">Create a sending profile</h4>

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

<h4 id="update-sending-profile">Update a sending profile</h4>

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

<h4 id="delete-sending-profile">Delete a sending profile</h4>

The system sending profile cannot be deleted.

`DELETE /sending-profiles/{id}`

```ts
type Request = {}
type Response = SendingProfile[] // the newsletter's remaining sending profiles
```

<h3 id="template">Template</h3>

Hyvor Post provides a flexible newsletter template system that allows you to customize the
appearance of your newsletters.

Endpoints:

- [`GET /templates`](#get-template) - Get newsletter template
- [`PATCH /templates`](#update-template) - Update newsletter template
- [`POST /templates/render`](#render-template) - Render newsletter template with content

Objects:

- [Template Object](#template-object)

<h4 id="get-template">Get newsletter template</h4>

`GET /templates`

```ts
type Request = {}
type Response = Template
```

<h4 id="update-template">Update newsletter template</h4>

`PATCH /templates`

```ts
type Request = {
    template?: string;
}
type Response = Template
```

<h4 id="render-template">Render newsletter template with content</h4>

`POST /templates/render`

```ts
type Request = {
    template?: string | null;
}
type Response = {
    html: string;
}
```

<h3 id="user">User</h3>

Admins of the organization that owns the newsletter can be added as users to collaborate on managing it.

Endpoints:

- [`GET /users`](#get-user) - Get user
- [`POST /users`](#create-user) - Create user
- [`DELETE /users`](#delete-user) - Delete user

Objects:

- [User Object](#user-object)

<h4 id="get-user">Get user</h4>

`GET /users`

```ts
type Request = {}
type Response = User[]
```

<h4 id="create-user">Create user</h4>

`POST /users`

The user must already be a member of the organization that owns this newsletter.

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
			Returns a 400 error if the user is not a member of the organization that owns this newsletter.
		</li>
	</ul>
</Callout>

<h4 id="delete-user">Delete user</h4>

`DELETE /users`

```ts
type Request = {
    // one of user_id or id is required
    user_id?: number; // the user's id in HYVOR (AuthInterface)
    id?: number; // the user's id in this newsletter's user list
}
type Response = {}
```

<h3 id="api-keys">API Keys</h3>

Every API key is granted one or more scopes, which limit what resources and actions it can access. Available scopes:

- `newsletter.read` / `newsletter.write` / `newsletter.delete`
- `issues.read` / `issues.write`
- `sending_profiles.read` / `sending_profiles.write`
- `subscribers.read` / `subscribers.write`
- `users.read` / `users.write`
- `templates.read` / `templates.write`
- `api_keys.read` / `api_keys.write`
- `media.write`
- `data.read` / `data.write` - subscriber lists, imports, and exports

Endpoints:

- [`GET /api-keys`](#get-api-keys) - Get API keys
- [`POST /api-keys`](#create-api-key) - Create an API key
- [`PATCH /api-keys/{id}`](#update-api-key) - Update an API key
- [`POST /api-keys/{id}`](#regenerate-api-key) - Regenerate an API key
- [`DELETE /api-keys/{id}`](#delete-api-key) - Delete an API key

Objects:

- [API Key Object](#api-key-object)

<h4 id="get-api-keys">Get API keys</h4>

The raw key is not returned; only its metadata is.

`GET /api-keys`

```ts
type Request = {}
type Response = ApiKey[]
```

<h4 id="create-api-key">Create an API key</h4>

`POST /api-keys`

```ts
type Request = {
    name: string;   // max length: 255
    scopes: string[]; // see the list of scopes above
}
type Response = ApiKey
```

<Callout type="warning">
	The <code>key</code> property (the raw key) is only returned once, on creation. Store it securely -
	it cannot be retrieved again.
</Callout>

<h4 id="update-api-key">Update an API key</h4>

`PATCH /api-keys/{id}`

```ts
type Request = {
    name?: string;   // max length: 255
    is_enabled?: boolean;
    scopes?: string[];
}
type Response = ApiKey
```

<h4 id="regenerate-api-key">Regenerate an API key</h4>

Regenerates the raw key of an API key. The previous key is invalidated immediately, and the new raw key is returned once.

`POST /api-keys/{id}`

```ts
type Request = {}
type Response = ApiKey
```

<h4 id="delete-api-key">Delete an API key</h4>

Requests made with the deleted key will be rejected immediately.

`DELETE /api-keys/{id}`

```ts
type Request = {}
type Response = {}
```

<h3 id="media">Media</h3>

Endpoints:

- [`POST /media`](#upload-media) - Upload media

Objects:

- [Media Object](#media-object)

<h4 id="upload-media">Upload media</h4>

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

<h3 id="export">Export</h3>

Endpoints:

- [`GET /export`](#get-exports) - Get subscriber exports
- [`POST /export`](#create-export) - Create a subscriber export

Objects:

- [Subscriber Export Object](#subscriber-export-object)

<h4 id="get-exports">Get subscriber exports</h4>

`GET /export`

```ts
type Request = {}
type Response = SubscriberExport[]
```

<h4 id="create-export">Create a subscriber export</h4>

`POST /export`

```ts
type Request = {}
type Response = SubscriberExport
```

<!-- ############################### OBJECTS ################################## -->

<h2 id="objects">Objects</h2>

<h3 id="newsletter-object">Newsletter Object</h3>

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
}
```

<h3 id="issue-object">Issue Object</h3>

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

<h3 id="send-object">Send Object</h3>

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

<h3 id="list-object">List Object</h3>

```ts
interface List {
    id: number;
    created_at: number; // unix timestamp
    name: string;
    description: string | null;
    subscribers_count: number;
}
```

<h3 id="subscriber-object">Subscriber Object</h3>

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

<h3 id="subscriber-metadata-definition-object">Subscriber Metadata Definition Object</h3>

```ts
interface SubscriberMetadataDefinition {
    id: number;
    created_at: number; // unix timestamp
    key: string;
    name: string;
    type: 'text'; // only 'text' is currently supported
}
```

<h3 id="sending-profile-object">Sending Profile Object</h3>

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

<h3 id="template-object">Template Object</h3>

```ts
interface Template {
    template: string;
}
```

<h3 id="user-mini-object">User Mini Object</h3>

```ts
interface UserMiniObject {
    name: string;
    email: string;
    username: string | null;
    picture_url: string | null;
}
```

<h3 id="user-object">User Object</h3>

```ts
interface User {
    id: number;
    role: 'owner' | 'admin';
    created_at: number; // unix timestamp
    user: UserMiniObject;
}
```

<h3 id="media-object">Media Object</h3>

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

<h3 id="subscriber-export-object">Subscriber Export Object</h3>

```ts
interface SubscriberExport {
    id: number;
    created_at: number; // unix timestamp
    status: 'pending' | 'completed' | 'failed';
    error_message: string | null;
    url: string | null;
}
```
