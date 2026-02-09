<script lang="ts">
	import GuideSection from "$lib/install/GuideSection.svelte";
	import { CodeBlock } from "@hyvor/design/components";
    import { newsletterStore } from "../../../../routes/console/lib/stores/newsletterStore";
	import { DocsImage } from "@hyvor/design/marketing";
    import codeWidgetImg from "$lib/install/guides/one/code-widget.png";
</script>

<h2 id="onecom">One.com</h2>

<p>
	Embed the signup form on your One.com website to allow visitors to subscribe to your newsletter directly from your site.
</p>

<br /><br />

<GuideSection>
	To embed the signup form on your One.com website, follow these steps:

    <ol>
        <li>In your One.com website builder dashboard, go to "<b>Widgets</b>" and select "<b>Custom Code</b>"</li>
        <DocsImage src={codeWidgetImg} alt="One.com Custom Code Widget" width={400} />
        <li>Drag it and drop on where you want the Hyvor Post signup form to appear on your site.</li>
        <li>Paste the following code snippet into the custom code widget:</li>
        <CodeBlock
    language="html"
    code={`
        <script src="https://post.hyvor.com/form/form.js" type="module" async><` + `/script>

        <hyvor-post-form newsletter="${$newsletterStore?.subdomain || '{subdomain}'}"></hyvor-post-form>
    `}
/>
        <li>Save and publish your changes to make the signup form live on your website.</li>
    </ol>
</GuideSection>

<p>
	
</p>
