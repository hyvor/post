<script lang="ts">
	import { CodeBlock } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
</script>

<h1>Signup Form</h1>

<p>
	You can embed Hyvor Post's signup form on your website to allow users to subscribe to your
	newsletter.
</p>

<h2 id="embed">Embed the Signup Form</h2>

<p>
	First, add the following script tag to <code>{`<head>`}</code> of your website:
</p>

<CodeBlock
	code={`
    <` +
		`script src="https://post.hyvor.com/form.js" async><` +
		`/script>
`}
/>

<p>Then, add the following HTML tag to the place you want to show the form:</p>

<CodeBlock
	code={`
    <hyvor-post-form newsletter="{subdomain}"></hyvor-post-form>
`}
/>

<p>
	Replace <code>{`{subdomain}`}</code> with your newsletter subdomain. You can find it at
	<strong>Console &rarr; Settings &rarr; Newsletter &rarr; Newsletter Subdomain</strong>.
</p>

<h2 id="multiple-lists">Working with Multiple Lists</h2>

<p>Assume you have two lists in your newsletter: Weekly Updates and Product Announcements.</p>

<p>
	1. By default, both lists will be shown in the signup form, and users can choose which list(s)
	to subscribe to.
</p>

<CodeBlock
	code={`
    <hyvor-post-form newsletter="{subdomain}"></hyvor-post-form>
`}
/>

<DocsImage src="/img/docs/form-multi-default.png" alt="Multiple Lists Form" width={400} />

<p>
	2. If you want to show only the Product Announcements list, you can set the
	<code>lists</code> attribute to "Product Announcements" (multiple lists can be separated by commas).
</p>

<CodeBlock
	code={`
<hyvor-post-form 
	newsletter="{subdomain}"
	lists="Product Announcements"
></hyvor-post-form>
`}
/>

<p>
	3. By default, all lists are selected. If you want to make a list unselected by default, you can
	set the <code>lists-default-unselected</code> attribute to the list name (again, multiple lists can
	be separated by commas).
</p>

<CodeBlock
	code={`
<hyvor-post-form
	newsletter="{subdomain}"
	lists-default-unselected="Product Announcements"
></hyvor-post-form>
`}
/>

<p>
	4. If you want to hide the list selection, you can set the <code>lists-hidden</code> attribute.
	The user will be subscribed to all lists by default. To subscribe to specific lists, use the
	<code>lists</code> attribute.
</p>

<CodeBlock
	code={`
<hyvor-post-form
	newsletter="{subdomain}"
	lists="Product Announcements"
	lists-hidden
></hyvor-post-form>
`}
/>

<p>
	In the above example, the user will be subscribed to the Product Announcements list without
	seeing the list selection.
</p>

<h2 id="attributes">Form Attributes</h2>

<h2 id="double-opt-in">Double Opt-In</h2>

<p>
	By default, the signup form uses double opt-in. Subscribers are added to your lists only after
	they confirm their subscription via the email they receive.
</p>
