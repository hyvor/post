<script lang="ts">
	import GuideSection from "$lib/install/GuideSection.svelte";
	import { Callout, CodeBlock } from "@hyvor/design/components";
    import { newsletterStore } from "../../../../routes/console/lib/stores/newsletterStore";
</script>

<h2 id="HTML">HTML</h2>


<p>
    HTML is the easiest way to integrate Hyvor Post signup form with any website. 
</p>

<br /><br />

<GuideSection>
	To embed the signup form on your website, follow these steps:

    <ol>
        <li>Copy the following code snippet.</li>
       <CodeBlock
    language="html"
    code={`
        <script src="https://post.hyvor.com/form/form.js" type="module" async><` + `/script>    
        <hyvor-post-form newsletter="${$newsletterStore?.subdomain || '{subdomain}'}"></hyvor-post-form>
    `}
    />
    <Callout type="info" title="Replace Subdomain">
        Make sure to replace <code>{'{subdomain}'}</code> with your actual newsletter subdomain.
    </Callout>
        <li>Paste the code snippet into the HTML of your website where you want the signup form to appear.</li>
    </ol>
</GuideSection>