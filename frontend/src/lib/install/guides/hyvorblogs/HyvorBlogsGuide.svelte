<script lang="ts">
	import GuideSection from "$lib/install/GuideSection.svelte";
	import { CodeBlock } from "@hyvor/design/components";
    import { newsletterStore } from "../../../../routes/console/lib/stores/newsletterStore";
</script>

<h2 id="hyvorblogs">Hyvor Blogs</h2>


<p>
	Integrate Hyvor Post signup form with your Hyvor Blogs blog to allow visitors to subscribe to your newsletter directly from your blog.
</p>

<br /><br />

<GuideSection>
	To embed the signup form on your Hyvor Blogs blog, follow these steps:

    <ol>
        <li>Log in to your <a href="https://blogs.hyvor.com/console">Hyvor Blogs console</a>.</li>
        <li>Go to <code>Settings -&gt; Comments & Newsletter -&gt; Newsletter Signup Form Code</code>.</li>
       <CodeBlock
    language="html"
    code={`
        <script src="https://post.hyvor.com/form/form.js" type="module" async><` + `/script>

        <hyvor-post-form newsletter="${$newsletterStore?.subdomain || '{subdomain}'}"></hyvor-post-form>
    `}
/>
        <li>Copy the above code snippet and paste on it.</li>
        <li>Save your changes to update your blog with the new signup form.</li>
    </ol>
</GuideSection>