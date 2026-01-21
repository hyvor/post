<script lang="ts">
	import GuideSection from "$lib/install/GuideSection.svelte";
	import { CodeBlock } from "@hyvor/design/components";
    import { newsletterStore } from "../../../../routes/console/lib/stores/newsletterStore";
	import { DocsImage } from "@hyvor/design/marketing";
    import addBlockImg from "$lib/install/guides/squarespace/add-block.png";
    import embedImg from "$lib/install/guides/squarespace/embed.png";
    import chooseCodeImg from "$lib/install/guides/squarespace/choose-code.png";
</script>

<h2 id="squarespace">Squarespace</h2>


<p>
	Integrate Hyvor Post signup form with your Squarespace site to allow visitors to subscribe to your newsletter directly from your site.
</p>

<br /><br />

<GuideSection>
	To embed the signup form on your Squarespace website, follow these steps:

    <ol>
       <li>Go to Squarespace dashboard and find the place where you want to add the signup form.</li>
       <DocsImage src={addBlockImg} alt="Squarespace Add Block" width={400} />
       <li>Click "<b>Add Block</b>" and choose "<b>Embed</b>".</li>
       <DocsImage src={embedImg} alt="Squarespace Embed" width={400} />
       <li>Choose "<b>Code</b>".</li>
        <DocsImage src={chooseCodeImg} alt="Squarespace Choose Code" width={400} />
        <li>Copy the following code snippet and paste it into the code block:</li>
        <CodeBlock language="html" code={`
            <script src="https://post.hyvor.com/form/form.js" type="module" async><` + `/script>

            <hyvor-post-form newsletter="${$newsletterStore?.subdomain || '{subdomain}'}"></hyvor-post-form>
        `} />
        <li>Save and publish your changes to make the signup form live on your website.</li>
    </ol>
</GuideSection>