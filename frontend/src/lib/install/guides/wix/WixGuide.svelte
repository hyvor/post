<script lang="ts">
	import GuideSection from "$lib/install/GuideSection.svelte";
	import { CodeBlock } from "@hyvor/design/components";
    import { newsletterStore } from "../../../../routes/console/lib/stores/newsletterStore";
	import { DocsImage } from "@hyvor/design/marketing";
    import addElementImg from "$lib/install/guides/wix/add-element.png";
    import pasteCodeImg from "$lib/install/guides/wix/paste-code.png";
    import doneImg from "$lib/install/guides/wix/done.png";
</script>

<h2 id="wix">Wix</h2>


<p>
	Integrate Hyvor Post signup form with your Wix site to allow visitors to subscribe to your newsletter directly from your site.
</p>

<br /><br />

<GuideSection>
	To embed the signup form on your Wix website, follow these steps:

    <ol>
       <li>Go to Wix dashboard and find the place where you want to add the signup form.</li>
       <li>Click "<b>Add Element</b>" and choose "<b>Embed Code</b>".</li>
       <DocsImage src={addElementImg} alt="Wix Add Element" width={400} />
       <li>Choose "<b>Embed HTML</b>".</li>
       <DocsImage src={pasteCodeImg} alt="Wix Paste Code" width={400} />
        <li>Copy the following code snippet and paste it under the <b>Code</b> section in the code block:</li>
        <CodeBlock language="html" code={`
            <script src="https://post.hyvor.com/form/form.js" type="module" async><` + `/script>

            <hyvor-post-form newsletter="${$newsletterStore?.subdomain || '{subdomain}'}"></hyvor-post-form>
        `} />
        <li>Save and publish your changes to make the signup form live on your website.</li>
          <DocsImage src={doneImg} alt="Wix Done" width={400} />
    </ol>
</GuideSection>