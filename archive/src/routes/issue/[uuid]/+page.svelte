<script lang="ts">
    import {onMount} from 'svelte';
    import {getIssueHtml} from '$lib/actions/archiveActions';
    import {page} from '$app/state';
    import type {Issue} from '$lib/types';
    import {Button} from '@hyvor/design/components';

    let issue = $state({} as Issue);
    let iframeEl: HTMLIFrameElement | null = null;

    function setHtml(html: string) {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const style = document.createElement('style');

        style.textContent = `
		    body {
                padding-top: 60px!important;
                padding-bottom: 60px!important;
		    }
		`;
        doc.body.appendChild(style);
        console.log(doc.documentElement.outerHTML);

        if (iframeEl) {
            iframeEl.srcdoc = doc.documentElement.innerHTML;
        }
    }

    onMount(() => {
        getIssueHtml(page.params.uuid).then((res) => {
            issue = res;
            setHtml(issue.html);
        });
    });
</script>

<svelte:head>
    <title>{issue.subject}</title>
    <meta name="description" content={issue.subject}/>
    <meta property="og:title" content={issue.subject}/>
    <meta property="og:description" content={issue.subject}/>
</svelte:head>

<div class="iframe-wrap">
    <iframe
        width="100%"
        height="100%"
        frameborder="0"
        sandbox="allow-same-origin"
        title={issue.subject}
        bind:this={iframeEl}
    ></iframe>
</div>

<div class="popup hds-box">
    <Button as="a">Go back to the newsletter</Button>
</div>

<style>
    :global(body) {
        overflow: hidden;
    }

    .iframe-wrap {
        position: relative;
        width: 100%;
        height: 100vh;
    }

    iframe {
        overflow: hidden;
    }

    .popup {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        padding: 12px;
        text-align: center;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
</style>
