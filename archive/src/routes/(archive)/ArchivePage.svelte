<script>
    import {issuesStore, newsletterStore} from '$lib/archiveStore';
    import ReadableDate from "../@components/ReadableDate.svelte";
    import Notice from "../@components/Notice.svelte";
    import IconSendSlash from '@hyvor/icons/IconSendSlash';
</script>

<div class="container">
    <div class="header">
        {$newsletterStore.name}
    </div>

    <div class="subscribe-form">
        <!-- TODO -->
    </div>

    <div class="issues hds-box">
        {#if $issuesStore.length === 0}
            <div class="no-issues">
                <Notice
                        heading="No issues sent yet"
                        message="The newsletter issues will appear here after they are sent."
                        icon={IconSendSlash}
                />
            </div>
        {:else}
            {#each $issuesStore as issue}
                <a class="issue" href={`/issue/${issue.uuid}`}>
                    <div class="subject">
                        {issue.subject}
                    </div>
                    <div class="sent-at">
                        <ReadableDate time={issue.sent_at}/>
                    </div>
                </a>
            {/each}
        {/if}
    </div>
</div>

<style>
    .container {
        color: var(--hp-box-text);
        width: 700px;
        margin: 0 auto;
        max-width: 100%;
        height: 100vh;
        overflow-y: auto;
    }

    .header {
        padding: 20px 0;
        font-size: 1.25em;
        font-weight: var(--hp-font-weight-heading);
    }

    .no-issues {
        height: 50vh;
    }

    .issues {
        min-height: 50vh;
        max-height: 85vh;
        overflow-y: auto;
        padding: 20px;
        background-color: var(--hp-box);
        box-shadow: var(--hp-box-shadow);
        border: var(--hp-box-border);
        border-radius: var(--hp-box-radius);
    }

    .issue {
        padding: 15px;
        display: block;
        border-radius: var(--hp-box-radius);
    }

    .issue:hover {
        background-color: color-mix(in srgb, var(--hp-box), #000000 5%);
    }

    .sent-at {
        color: var(--hp-box-text-light);
        font-size: 0.8em
    }
</style>
