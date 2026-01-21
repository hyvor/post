<script lang="ts">
	import GuideSection from "$lib/install/GuideSection.svelte";
	import { CodeBlock } from "@hyvor/design/components";
    import { newsletterStore } from "../../../../routes/console/lib/stores/newsletterStore";
	import { DocsImage } from "@hyvor/design/marketing";
    import embedCodeImg from "$lib/install/guides/framer/embed-code.png";
    import pasteCodeImg from "$lib/install/guides/framer/paste-code.png";
</script>

<h2 id="framer">Framer</h2>


<p>
	Integrate Hyvor Post signup form with your Framer site to allow your users to subscribe to your newsletter directly from your website.
</p>

<br /><br />

<GuideSection>
	To embed the signup form on your Framer website, follow these steps:

    <ol>
        <li>In your Framer project editor, go to "<b>Utility</b>" and choose "<b>Embed</b>" component and place it on the place you want the signup form to appear.</li>
        <DocsImage src={embedCodeImg} alt="Framer Embed Component" width={400} />
        <li>Click on the embed component and in the properties panel, paste the following code snippet in the "<b>HTML</b>" section.</li>
        <DocsImage src={pasteCodeImg} alt="Framer Paste Code" width={400} />
        <CodeBlock language="html" code={`
        <script src="https://post.hyvor.com/form/form.js" type="module" async><` + `/script>

        <hyvor-post-form newsletter="${$newsletterStore?.subdomain || '{subdomain}'}"></hyvor-post-form>
        `} />
        <li>Save the changes.</li>
    </ol>
</GuideSection>
