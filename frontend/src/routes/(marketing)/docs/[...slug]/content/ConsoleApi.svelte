<script lang="ts">
	import { Callout, CodeBlock } from '@hyvor/design/components';
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
	<li><a href="#sending-profile">Sending Profile</a></li>
	<li><a href="#subscriber-metadata">Subscriber Metadata</a></li>
	<li><a href="#template">Template</a></li>
	<li><a href="#domain">Domain</a></li>
	<li><a href="#user">User</a></li>
	<li><a href="#media">Media</a></li>
	<li><a href="#export">Export</a></li>
</ul>

<h3 id="newsletter">Newsletter</h3>

<p>Endpoints:</p>

<ul>
	<li><a href="#get-newsletter"><code>GET /newsletter</code></a> - Get newsletter data</li>
	<li>
		<a href="#update-newsletter"><code>PATCH /newsletter</code></a> - Update
		a newsletter
	</li>
	<li>
		<a href="#delete-newsletter"><code>DELETE /newsletter</code></a> - Delete
		a newsletter
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

<h4 id="delete-newsletter">Delete a newsletter</h4>

<code>DELETE /newsletter</code>

<CodeBlock
    language="ts"
    code={`
        type Request = {}
        type Response = {}
    `}
/>

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
