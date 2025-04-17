<script lang="ts">
	import { CodeBlock } from '@hyvor/design/components';
</script>

<h1>Signup Form</h1>

<p>
	You can embed Hyvor Post's signup form on your website to allow users to subscribe to your
	lists.
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
    <hyvor-post-form
        project="YOUR_PROJECT_UUID"
    ></hyvor-post-form>
`}
/>

<p>
	Replace <code>YOUR_PROJECT_UUID</code> with your project UUID. You can find it at
	<strong>Console &rarr; Settings &rarr; Project &rarr; Project UUID</strong>.
</p>

<h2 id="attributes">Form Attributes</h2>
