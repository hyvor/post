<script lang="ts">
	import { Table, TableRow } from '@hyvor/design/components';
	import { DocsImage } from '@hyvor/design/marketing';
	import imgFormCustomize from '../images/form-customize.png';
	import imgFormMultiDefault from '../images/form-multi-default.png';
	import imgFormListsFilter from '../images/form-lists-filter.png';
	import imgFormListsUnselected from '../images/form-lists-unselected.png';
	import imgFormListsHidden from '../images/form-lists-hidden.png';
</script>

# Signup Form

You can embed Hyvor Post's signup form on your website to allow users to subscribe to your newsletter. The form uses double opt-in by default, ensuring that subscribers confirm their subscription via email.

- [Embed the Signup Form](#embed)
- [Customize the Form](#customize)
- [Customize the Confirmation Email](#confirmation-email)
- [Working with Multiple Lists](#multiple-lists)
- [Form Attributes](#attributes)

<h2 id="embed">Embed the Signup Form</h2>

First, add the following script tag to `<head>` of your website:

```html
<script src="https://post.hyvor.com/form/form.js" type="module" async></script>
```

Then, add the following HTML tag to the place you want to show the form:

```html
<hyvor-post-form newsletter="{subdomain}"></hyvor-post-form>
```

Replace `{subdomain}` with your newsletter subdomain. You can find it at **Console &rarr; Settings &rarr; Newsletter &rarr; Newsletter Subdomain**.

<h2 id="customize">Customize the Form</h2>

You can customize the text and appearance of the form at **Console &rarr; Settings &rarr; Signup Form**.

<DocsImage src={imgFormCustomize} alt="Customize form" />

This includes options like title, description, button text, colors and UI. Custom CSS can also be added for further customization. Note that the custom CSS is added to the form, which is a web component with a shadow DOM, so your custom CSS will only affect the form and not the rest of your website.

<h2 id="confirmation-email">Customize the Confirmation Email</h2>

When someone subscribes, they receive an email asking them to confirm their subscription. You can customize its subject and content at **Console &rarr; Settings &rarr; Confirmation Email**. Click **Edit Content** to open the editor, with a live preview of the email.

The following placeholders are available in the subject and the content:

- `{{newsletter_name}}` - the name of your newsletter
- `{{confirm_url}}` - the confirmation link. The content must contain a button or a link to this URL.

Click **Reset to Default** to go back to the default email.

<h2 id="multiple-lists">Working with Multiple Lists</h2>

Assume you have two lists in your newsletter: Weekly Updates and Product Announcements.

1. By default, both lists will be shown in the signup form, and users can choose which list(s) to subscribe to.

```html
<hyvor-post-form newsletter="{subdomain}"></hyvor-post-form>
```

<DocsImage src={imgFormMultiDefault} alt="Multiple Lists Form" width={400} />

2. If you want to show only the Product Announcements list, you can set the `lists` attribute to "Product Announcements" (multiple lists can be separated by commas). Anyone who subscribes via this form will be subscribed to the Product Announcements list only.

```html
<hyvor-post-form
	newsletter="{subdomain}"
	lists="Product Announcements"
></hyvor-post-form>
```

<DocsImage src={imgFormListsFilter} alt="Multiple Lists: Filtering" width={400} />

3. By default, all lists are selected in the form UI. If you want to make a list unselected by default, you can set the `lists-default-unselected` attribute to the list name (again, multiple lists can be separated by commas).

```html
<hyvor-post-form
	newsletter="{subdomain}"
	lists-default-unselected="Product Announcements"
></hyvor-post-form>
```

<DocsImage src={imgFormListsUnselected} alt="Multiple Lists: Unselected" width={400} />

4. If you want to hide the list selection, you can set the `lists-hidden` attribute. The user will be subscribed to all lists by default. To subscribe to specific lists, use the `lists` attribute.

```html
<hyvor-post-form
	newsletter="{subdomain}"
	lists="Product Announcements"
	lists-hidden
></hyvor-post-form>
```

<DocsImage src={imgFormListsHidden} alt="Multiple Lists: Hidden" width={400} />

In the above example, the user will be subscribed to the Product Announcements list without seeing the list selection.

<h2 id="attributes">Form Attributes</h2>

<Table columns="1fr 2fr">
	<TableRow head>
		<div>Attribute</div>
		<div>Value</div>
	</TableRow>
	<TableRow>
		<div><code>newsletter</code></div>
		<div>Your newsletter subdomain (required)</div>
	</TableRow>
	<TableRow>
		<div><code>lists</code></div>
		<div>
			A comma-separated list of newsletter list names to show in the form. If not set, all lists
			will be shown (if there are more than one).
		</div>
	</TableRow>
	<TableRow>
		<div><code>lists-default-unselected</code></div>
		<div>
			A comma-separated list of newsletter list names that should be unselected by default in the
			form. If not set, all lists will be selected by default.
		</div>
	</TableRow>
	<TableRow>
		<div><code>lists-hidden</code></div>
		<div>
			If set, the list selection will be hidden and users will be subscribed to all lists by
			default. To subscribe to specific lists, use the <code>lists</code> attribute.
		</div>
	</TableRow>
	<TableRow>
		<div><code>colors</code></div>
		<div>
			<code>light</code>, <code>dark</code> or <code>os</code>
		</div>
	</TableRow>
</Table>
