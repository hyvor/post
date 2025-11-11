<script lang="ts">
    import {CodeBlock, Table, TableRow} from '@hyvor/design/components';
    import {DocsImage} from '@hyvor/design/marketing';
</script>

<h1>Signup Form</h1>

<p>
    You can embed Hyvor Post's signup form on your website to allow users to subscribe to your
    newsletter. The form uses double opt-in by default, ensuring that subscribers confirm their
    subscription via email.
</p>

<ul>
    <li>
        <a href="#embed">Embed the Signup Form</a>
    </li>
    <li>
        <a href="#customize">Customize the Form</a>
    </li>
    <li>
        <a href="#multiple-lists">Working with Multiple Lists</a>
    </li>
    <li>
        <a href="#attributes">Form Attributes</a>
    </li>
</ul>

<h2 id="embed">Embed the Signup Form</h2>

<p>
    First, add the following script tag to <code>{`<head>`}</code> of your website:
</p>

<CodeBlock
        code={`
    <` +
		`script src="https://post.hyvor.com/form/form.js" async><` +
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

<h2 id="customize">Customize the Form</h2>

<p>
    You can customize the text and appearance of the form at <strong
>Console &rarr; Settings &rarr; Signup Form</strong
>.
</p>

<DocsImage src="/img/docs/form-customize.png" alt="Customize form"/>

<p>
    This includes options like title, description, button text, colors and UI. Custom CSS can also
    be added for further customization. Note that the custom CSS is added to the form, which is a
    web component with a shadow DOM, so your custom CSS will only affect the form and not the rest
    of your website.
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

<DocsImage src="/img/docs/form-multi-default.png" alt="Multiple Lists Form" width={400}/>

<p>
    2. If you want to show only the Product Announcements list, you can set the
    <code>lists</code> attribute to "Product Announcements" (multiple lists can be separated by commas).
    Anyone who subscribes via this form will only be subscribed to the Product Announcements list only.
</p>

<CodeBlock
        code={`
<hyvor-post-form 
	newsletter="{subdomain}"
	lists="Product Announcements"
></hyvor-post-form>
`}
/>

<DocsImage src="/img/docs/form-lists-filter.png" alt="Multiple Lists: Filtering" width={400}/>

<p>
    3. By default, all lists are selected in the form UI. If you want to make a list unselected by
    default, you can set the <code>lists-default-unselected</code> attribute to the list name (again,
    multiple lists can be separated by commas).
</p>

<CodeBlock
        code={`
<hyvor-post-form
	newsletter="{subdomain}"
	lists-default-unselected="Product Announcements"
></hyvor-post-form>
`}
/>

<DocsImage src="/img/docs/form-lists-unselected.png" alt="Multiple Lists: Unselected" width={400}/>

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

<DocsImage src="/img/docs/form-lists-hidden.png" alt="Multiple Lists: Hidden" width={400}/>

<p>
    In the above example, the user will be subscribed to the Product Announcements list without
    seeing the list selection.
</p>

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
    <!--
    Undocumented attribute
    <TableRow>
        <div><code>instance</code></div>
        <div>
            The Hyvor Post instance URL. Default is
            <code>https://post.hyvor.com</code>. Change this only if you are using a self-hosted
            instance or a different Hyvor Post instance.
        </div>
    </TableRow> -->
    <TableRow>
        <div><code>lists</code></div>
        <div>
            A comma-separated list of newsletter list names to show in the form. If not set, all
            lists will be shown (if there are more than one).
        </div>
    </TableRow>
    <TableRow>
        <div><code>lists-default-unselected</code></div>
        <div>
            A comma-separated list of newsletter list names that should be unselected by default in
            the form. If not set, all lists will be selected by default.
        </div>
    </TableRow>
    <TableRow>
        <div><code>lists-hidden</code></div>
        <div>
            If set, the list selection will be hidden and users will be subscribed to all lists by
            default. To subscribe to specific lists, use the <code>lists</code> attribute.
        </div>
    </TableRow>
</Table>
