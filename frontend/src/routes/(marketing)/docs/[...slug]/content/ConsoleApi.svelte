<script lang="ts">
    import {Callout, CodeBlock} from '@hyvor/design/components';
</script>

<h1>Console API</h1>

<p>
    The Console API allows you to automate your newsletter-related tasks over HTTP with API key
    authentication. This is the same API that we internally use at the Console.
</p>

<h2 id="getting-started">Getting Started</h2>

<ul>
    <li>
        Create a Console API key at <strong>Console &rarr; Settings &rarr; API Keys</strong>.
    </li>
    <li>The base URL: <code>https://post.hyvor.com/api/console</code></li>
    <li>
        For each request, set <code>Authorization</code> header to
        <code>Bearer {'<API_KEY>'}</code>.
    </li>
    <li>Available HTTP methods:</li>
    <ul>
        <li><code>GET</code> - Retrieve a resource</li>
        <li><code>POST</code> - Create a resource or perform an action</li>
        <li><code>PUT</code> - Update a resource</li>
        <li><code>DELETE</code> - Remove a resource</li>
    </ul>
    <li>
        Request params can be set as <code>JSON</code> (recommended) or as
        <code>application/x-www-form-urlencoded</code>.
    </li>
    <li>All endpoints return JSON data. The response will be an object or an array of objects.</li>
</ul>

<Callout type="info">
    <p>
        In this documentation, all objects, request params, and responses are written as <a
            href="https://www.typescriptlang.org/"
            rel="nofollow">Typescript</a
    > interfaces in order to make type declarations concise.
    </p>
</Callout>

<h2 id="categories">Categories</h2>

<p>The Console API endpoints are categorized based on the resource they interact with.</p>

<p>Jump to each category:</p>

<ul>
    <li><a href="#newsletter">Newsletter</a></li>
    <li><a href="#issue">Issue</a></li>
    <li><a href="#list">List</a></li>
    <li><a href="#subscriber">Subscriber</a></li>
    <li><a href="#subscriber-metadata">Subscriber Metadata</a></li>
    <li><a href="#sending-profile">Sending Profile</a></li>
    <li><a href="#template">Template</a></li>
    <li><a href="#user">User</a></li>
    <li><a href="#media">Media</a></li>
    <li><a href="#export">Export</a></li>
</ul>

<!-- ############################## CATEGORIES ################################# -->

<h3 id="newsletter">Newsletter</h3>

<p>Endpoints:</p>

<ul>
    <li><a href="#get-newsletter"><code>GET /newsletter</code></a> - Get newsletter data</li>
    <li>
        <a href="#update-newsletter"><code>PATCH /newsletter</code></a> - Update a newsletter
    </li>
</ul>

<p>Objects:</p>

<ul>
    <li><a href="#newsletter-object">Newsletter Object</a></li>
</ul>

<h4 id="get-newsletter">Get newsletter data</h4>

<code>GET /newsletter</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = Newsletter
    `}
/>

<h4 id="update-newsletter">Update a newsletter</h4>

<code>PATCH /newsletter</code>

<CodeBlock
        language="ts"
        code={`
        type Request = Partial<Newsletter>  // except id, created_at
        type Response = Newsletter
    `}
/>

<h3 id="issue">Issue</h3>

<p>Endpoints:</p>

<ul>
    <li><a href="#get-issues"><code>GET /issues</code></a> - Get issues</li>
    <li><a href="#create-issue"><code>POST /issues</code></a> - Create an issue</li>
    <li><a href="#get-issue"><code>GET /issues/{'{id}'}</code></a> - Get an issue</li>
    <li><a href="#update-issue"><code>PATCH /issues/{'{id}'}</code></a> - Update an issue</li>
    <li><a href="#delete-issue"><code>DELETE /issues/{'{id}'}</code></a> - Delete an issue</li>
    <li><a href="#send-issue"><code>POST /issues/{'{id}'}/send</code></a> - Send an issue</li>
    <li>
        <a href="#get-issue-sends"><code>GET /issues/{'{id}'}/sends</code></a> - Get issue sends
    </li>
</ul>

<p>Objects:</p>

<ul>
    <li><a href="#issue-object">Issue Object</a></li>
    <li><a href="#send-object">Send Object</a></li>
</ul>

<h4 id="get-issues">Get issues</h4>

<code>GET /issues</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            limit?: number; // default: 50
            offset?: number; // default: 0
        }
        type Response = Issue[]
    `}
/>

<h4 id="create-issue">Create an issue</h4>

<code>POST /issues</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = Issue
    `}
/>

<h4 id="get-issue">Get an issue</h4>

<code>GET /issues/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = Issue
    `}
/>

<h4 id="update-issue">Update an issue</h4>

<code>PATCH /issues/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            subject?: string;
            lists?: number[];
            content?: string;
            sending_profile_id?: number;
        }
        type Response = Issue
    `}
/>

<h4 id="delete-issue">Delete an issue</h4>

<code>DELETE /issues/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = {}
    `}
/>

<h4 id="send-issue">Send an issue</h4>

<code>POST /issues/{'{id}'}/send</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = Issue
    `}
/>

<h4 id="get-issue-sends">Get issue sends</h4>

<code>GET /issues/{'{id}'}/sends</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = Send[]
    `}
/>

<h3 id="lists">Lists</h3>

<p>Endpoints:</p>

<ul>
    <li><a href="#create-list"><code>POST /lists</code></a> - Create a list</li>
    <li><a href="#update-list"><code>PATCH /lists/{'{id}'}</code></a> - Update a list</li>
    <li><a href="#delete-list"><code>DELETE /lists/{'{id}'}</code></a> - Delete a list</li>
</ul>

<p>Objects:</p>

<ul>
    <li><a href="#list-object">List Object</a></li>
</ul>

<h4 id="create-list">Create a list</h4>

<code>POST /lists</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            name: string;   // max length: 255
            description?: string;
        }
        type Response = List
    `}
/>

<h4 id="update-list">Update a list</h4>

<code>PATCH /lists/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            name?: string;   // max length: 255
            description?: string;
        }
        type Response = List
    `}
/>

<h4 id="delete-list">Delete a list</h4>

<code>DELETE /lists/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = {}
    `}
/>

<h3 id="subscriber">Subscriber</h3>

<p>Endpoints:</p>

<ul>
    <li><a href="#get-subscribers"><code>GET /subscribers</code></a> - Get subscribers</li>
    <li><a href="#create-subscriber"><code>POST /subscribers</code></a> - Create a subscriber</li>
    <li>
        <a href="#update-subscriber"><code>PATCH /subscribers/{'{id}'}</code></a> - Update a subscriber
    </li>
    <li>
        <a href="#delete-subscriber"><code>DELETE /subscribers/{'{id}'}</code></a> - Delete a subscriber
    </li>
    <li>
        <a href="#bulk-update-subscriber"><code>POST /subscribers/bulk</code></a> - Bulk update subscribers
    </li>
</ul>

<p>Objects:</p>
<ul>
    <li><a href="#subscriber-object">Subscriber Object</a></li>
</ul>

<h4 id="get-subscribers">Get subscribers</h4>

<code>GET /subscribers</code>

<CodeBlock
        language="ts"
        code={`
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
    `}
/>

<h4 id="create-subscriber">Create a subscriber</h4>

<code>POST /subscribers</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            email: string;
            list_ids: number[];
            source?: 'console' | 'form' | 'import'; // default: 'console'
            subscribe_ip?: string | null;
            subscribed_at?: number | null; // unix timestamp
            unsubscribed_at?: number | null; // unix timestamp
        }
        type Response = Subscriber
    `}
/>

<h4 id="update-subscriber">Update a subscriber</h4>

<code>PATCH /subscribers/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            email?: string;
            list_ids?: number[];
            status?: 'subscribed' | 'unsubscribed' | 'pending';
            metadata?: Record<string, string>;
        }
        type Response = Subscriber
    `}
/>

<h4 id="delete-subscriber">Delete a subscriber</h4>

<code>DELETE /subscribers/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = {}
    `}
/>

<h4 id="bulk-update-subscriber">Bulk update subscribers</h4>

<code>POST /subscribers/bulk</code>

<CodeBlock
        language="ts"
        code={`
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
    `}
/>

<h3 id="subscriber-metadata">Subscriber Metadata</h3>

<p>
    Subscriber metadata definitions allow you to define custom fields for subscribers. These fields
    can be used to store additional information about subscribers.
</p>

<p>Endpoints:</p>

<ul>
    <li>
        <a href="#create-subscriber-metadata-definition"
        ><code>POST /subscriber-metadata-definitions</code></a
        > - Create a subscriber metadata definition
    </li>
    <li>
        <a href="#update-subscriber-metadata-definition"
        ><code>PATCH /subscriber-metadata-definitions/{'{id}'}</code></a
        > - Update a subscriber metadata definition
    </li>
    <li>
        <a href="#delete-subscriber-metadata-definition"
        ><code>DELETE /subscriber-metadata-definitions/{'{id}'}</code></a
        > - Delete a subscriber metadata definition
    </li>
</ul>

<p>Objects:</p>

<ul>
    <li>
        <a href="#subscriber-metadata-definition-object">Subscriber Metadata Definition Object</a>
    </li>
</ul>

<h4 id="create-subscriber-metadata-definition">Create a subscriber metadata definition</h4>

<code>POST /subscriber-metadata-definitions</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            key: string;   // max length: 255
            name: string;  // max length: 255
        }
        type Response = SubscriberMetadataDefinition
    `}
/>

<Callout type="info">
    <ul>
        <li><code>key</code> can only contain lowercase letters, numbers, and underscores.</li>
        <li>Once created, the <code>key</code> cannot be changed.</li>
    </ul>
</Callout>

<h4 id="update-subscriber-metadata-definition">Update a subscriber metadata definition</h4>

<code>PATCH /subscriber-metadata-definitions/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            name: string;  // max length: 255
        }
        type Response = SubscriberMetadataDefinition
    `}
/>

<h4 id="delete-subscriber-metadata-definition">Delete a subscriber metadata definition</h4>

<code>DELETE /subscriber-metadata-definitions/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = {}
    `}
/>

<h3 id="sending-profile">Sending Profile</h3>

<p>Endpoints:</p>

<ul>
    <li>
        <a href="#get-sending-profiles"><code>GET /sending-profiles</code></a> - Get sending profiles
    </li>
    <li>
        <a href="#create-sending-profile"><code>POST /sending-profiles</code></a> - Create a sending
        profile
    </li>
    <li>
        <a href="#update-sending-profile"><code>PATCH /sending-profiles/{'{id}'}</code></a> - Update
        a sending profile
    </li>
    <li>
        <a href="#delete-sending-profile"><code>DELETE /sending-profiles/{'{id}'}</code></a> - Delete
        a sending profile
    </li>
</ul>

<p>Objects:</p>

<ul>
    <li><a href="#sending-profile-object">Sending Profile Object</a></li>
</ul>

<h4 id="get-sending-profiles">Get sending profiles</h4>

<code>GET /sending-profiles</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = SendingProfile[]
    `}
/>

<h4 id="create-sending-profile">Create a sending profile</h4>

<code>POST /sending-profiles</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            from_email: string;
            from_name?: string | null;
            reply_to_email?: string | null;
            brand_name?: string | null;
            brand_logo?: string | null; // a publicly accessible URL of the logo
            brand_url?: string | null;
        }
        type Response = SendingProfile
    `}
/>

<h4 id="update-sending-profile">Update a sending profile</h4>

<code>PATCH /sending-profiles/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            from_email?: string;
            from_name?: string | null;
            reply_to_email?: string | null;
            brand_name?: string | null;
            brand_logo?: string | null; // a publicly accessible URL of the logo
            brand_url?: string | null;
            is_default?: true;
        }
        type Response = SendingProfile
    `}
/>

<h4 id="delete-sending-profile">Delete a sending profile</h4>

<code>DELETE /sending-profiles/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = {}
    `}
/>

<h3 id="template">Template</h3>

Hyvor Post provides a flexible newsletter template system that allows you to customize the
appearance of your newsletters.

<p>Endpoints:</p>

<ul>
    <li><a href="#get-template"><code>GET /templates</code></a> - Get newsletter template</li>
    <li>
        <a href="#update-template"><code>PATCH /templates</code></a> - Update newsletter template
    </li>
    <li>
        <a href="#render-template"><code>POST /templates/render</code></a> - Render newsletter template
        with content
    </li>
</ul>

<p>Objects:</p>

<ul>
    <li><a href="#template-object">Template Object</a></li>
</ul>

<h4 id="get-template">Get newsletter template</h4>

<code>GET /templates</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = Template
    `}
/>

<h4 id="update-template">Update newsletter template</h4>

<code>PATCH /templates</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            template?: string;
        }
        type Response = Template
    `}
/>

<h4 id="render-template">Render newsletter template with content</h4>

<code>POST /templates/render</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            template?: string | null;
        }
        type Response = {
            html: string;
        }
    `}
/>

<h3 id="user">User</h3>

<p>
    The owner of the newsletter can invite other users as Admins to collaborate on managing the
    newsletter.
</p>

<p>Endpoints:</p>

<ul>
    <li><a href="#get-user"><code>GET /users</code></a> - Get user</li>
    <li><a href="#delete-user"><code>DELETE /users/{'{id}'}</code></a> - Delete user</li>
    <li><a href="#get-invites"><code>GET /invites</code></a> - Get invites</li>
    <li><a href="#create-invite"><code>POST /invites</code></a> - Create an invite</li>
    <li><a href="#delete-invite"><code>DELETE /invites/{'{id}'}</code></a> - Delete an invite</li>
</ul>

<p>Objects:</p>

<ul>
    <li><a href="#user-object">User Object</a></li>
    <li><a href="#user-invite-object">User Invite Object</a></li>
</ul>

<h4 id="get-user">Get user</h4>

<code>GET /users</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = User[]
    `}
/>

<h4 id="delete-user">Delete user</h4>

<code>DELETE /users/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = {}
    `}
/>

<h4 id="get-invites">Get invites</h4>

<code>GET /invites</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = UserInvite[]
    `}
/>

<h4 id="create-invite">Create an invite</h4>

<code>POST /invites</code>

<p>
    You must ask your Admins to create a <a href="https://hyvor.com/signup" rel="noreferrer"
>HYVOR account</a
> before sending an invitation.
</p>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            username?: string;
            email?: string;
        }
        type Response = UserInvite
    `}
/>

<Callout type="info">
    <ul>
        <li>
            Either <code>username</code> or <code>email</code> of the invitee's HYVOR account is required.
        </li>
    </ul>
</Callout>

<h4 id="delete-invite">Delete an invite</h4>

<code>DELETE /invites/{'{id}'}</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = {}
    `}
/>

<h3 id="media">Media</h3>

<p>Endpoints:</p>

<ul>
    <li><a href="#upload-media"><code>POST /media</code></a> - Upload media</li>
</ul>

<p>Objects:</p>

<ul>
    <li><a href="#media-object">Media Object</a></li>
</ul>

<h4 id="upload-media">Upload media</h4>

<code>POST /media</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {
            // max size: 10MB
            // supported formats: jpg, jpeg, png, gif, webp
            file: File; 
            folder: 'issue_images' | 'newsletter_images';
        }
        type Response = Media
    `}
/>

<h3 id="export">Export</h3>

<p>Endpoints:</p>

<ul>
    <li><a href="#get-exports"><code>GET /export</code></a> - Get subscriber exports</li>
    <li><a href="#create-export"><code>POST /export</code></a> - Create a subscriber export</li>
</ul>

<p>Objects:</p>

<ul>
    <li><a href="#subscriber-export-object">Subscriber Export Object</a></li>
</ul>

<h4 id="get-exports">Get subscriber exports</h4>

<code>GET /export</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = SubscriberExport[]
    `}
/>

<h4 id="create-export">Create a subscriber export</h4>

<code>POST /export</code>

<CodeBlock
        language="ts"
        code={`
        type Request = {}
        type Response = SubscriberExport
    `}
/>

<!-- ############################### OBJECTS ################################## -->

<h2 id="objects">Objects</h2>

<h3 id="newsletter-object">Newsletter Object</h3>

<CodeBlock
        language="ts"
        code={`
        interface Newsletter {
            id: string;
            subdomain: string;
            created_at: number; // unix timestamp
            name: string;

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
    `}
/>

<h3 id="issue-object">Issue Object</h3>

<CodeBlock
        language="ts"
        code={`
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
    `}
/>

<h3 id="send-object">Send Object</h3>

<CodeBlock
        language="ts"
        code={`
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
    `}
/>

<h3 id="list-object">List Object</h3>

<CodeBlock
        language="ts"
        code={`
        interface List {
            id: number;
            created_at: number; // unix timestamp
            name: string;
            description: string | null;
            subscribers_count: number;
        }
    `}
/>

<h3 id="subscriber-object">Subscriber Object</h3>

<CodeBlock
        language="ts"
        code={`
        interface Subscriber {
            id: number;
            email: string;
            source: 'console' | 'form' | 'import';
            status: 'subscribed' | 'unsubscribed' | 'pending';
            list_ids: number[];
            subscribe_ip: string | null;
            is_opted_in: boolean;
            subscribed_at: number | null; // unix timestamp
            unsubscribed_at: number | null; // unix timestamp
            metadata: Record<string, string>;
        }
    `}
/>

<h3 id="subscriber-metadata-definition-object">Subscriber Metadata Definition Object</h3>

<CodeBlock
        language="ts"
        code={`
        interface SubscriberMetadataDefinition {
            id: number;
            created_at: number; // unix timestamp
            key: string;
            name: string;
            type: 'text'; // only 'text' is currently supported
        }
    `}
/>

<h3 id="sending-profile-object">Sending Profile Object</h3>

<CodeBlock
        language="ts"
        code={`
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
    `}
/>

<h3 id="template-object">Template Object</h3>

<CodeBlock
        language="ts"
        code={`
        interface Template {
            template: string;
        }
    `}
/>

<h3 id="user-mini-object">User Mini Object</h3>

<CodeBlock
        language="ts"
        code={`
        interface UserMiniObject {
            name: string;
            email: string;
            username: string | null;
            picture_url: string | null;
        }
    `}
/>

<h3 id="user-object">User Object</h3>

<CodeBlock
        language="ts"
        code={`
        interface User {
            id: number;
            role: 'owner' | 'admin';
            created_at: number; // unix timestamp
            user: UserMiniObject;
        }
    `}
/>

<h3 id="user-invite-object">User Invite Object</h3>

<CodeBlock
        language="ts"
        code={`
        interface UserInvite {
            id: number;
            created_at: number; // unix timestamp
            role: 'admin';
            user: UserMiniObject;
            expires_at: number; // unix timestamp
        }
    `}
/>

<h3 id="media-object">Media Object</h3>

<CodeBlock
        language="ts"
        code={`
        interface Media {
            id: number;
            created_at: number; // unix timestamp
            folder: 'issue_images' | 'newsletter_images' | 'import' | 'export';
            url: string;
            size: number; // in bytes
            extension: string;
        }
    `}
/>

<h3 id="subscriber-export-object">Subscriber Export Object</h3>

<CodeBlock
        language="ts"
        code={`
        interface SubscriberExport {
            id: number;
            created_at: number; // unix timestamp
            status: 'pending' | 'completed' | 'failed';
            error_message: string | null;
            url: string | null;
        }
    `}
/>
