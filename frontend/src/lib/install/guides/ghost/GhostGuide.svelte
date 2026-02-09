<script lang="ts">
	import GuideSection from "$lib/install/GuideSection.svelte";
	import { CodeBlock } from "@hyvor/design/components";
    import { newsletterStore } from "../../../../routes/console/lib/stores/newsletterStore";
</script>

<h2 id="ghost">Ghost</h2>


<p>
	Integrate Hyvor Post signup form with your Ghost site to allow visitors to subscribe to your newsletter directly from your website.
</p>

<br /><br />

<GuideSection>
	To embed the signup form on your Ghost site, follow these steps:

    <ol>
        <li>To add Hyvor Post to Ghost, you need to edit your theme. If you are not sure how to do it, check <a href="https://ghost.org/tutorials/download-and-upload-a-theme/">Ghost's documentation</a>. After downloading your theme, open the template file you want to add comments to. Usually, it is <code>post.hbs</code>.</li>
        <li>Add the following code where you want to add comments.</li>
       <CodeBlock
    language="html"
    code={`
        <script src="https://post.hyvor.com/form/form.js" type="module" async><` + `/script>

        <hyvor-post-form newsletter="${$newsletterStore?.subdomain || '{subdomain}'}"></hyvor-post-form>
    `}
/>
        <li>Copy the above code snippet and paste on it.</li>
        <li>Save your changes.</li>
    </ol>
</GuideSection>