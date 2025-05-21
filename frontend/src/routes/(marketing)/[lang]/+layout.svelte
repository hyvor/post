<script lang="ts">
	import { InternationalizationProvider } from '@hyvor/design/components';
	import Footer from './Footer.svelte';
	import Header from "./Header.svelte";
	import type { PageProps } from '../$types';
	import { MARKETING_LANGUAGES, replaceLanguageCodeInUrl } from './locale';
	import { page } from '$app/state';

    interface Props {
        children?: import('svelte').Snippet;
    }

    let { children, data }: Props & PageProps = $props();
    
    const lang = (data as { lang: string }).lang;
</script>

<svelte:head>

    {#each MARKETING_LANGUAGES as lang}
        <link rel="alternate" href={replaceLanguageCodeInUrl(page.url.pathname, lang.code)} hreflang={lang.code} />
    {/each}

</svelte:head>
<InternationalizationProvider
    forceLanguage={lang}
    languages={MARKETING_LANGUAGES}
>

    <Header />

    {@render children?.()}

    <div class="footer-wrap">
        <Footer />
    </div>

</InternationalizationProvider>


<style>
    .footer-wrap {
        margin-top: 100px;
    }
</style>