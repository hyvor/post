<script>
    import {issuesStore, newsletterStore} from '$lib/archiveStore';
    import ReadableDate from "../@components/ReadableDate.svelte";
</script>

<div class="container">
    <div class="header">
        {$newsletterStore.name}
    </div>

    <div class="subscribe-form">
        <!-- TODO -->
    </div>

    <div class="issues hds-box">
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
    </div>
</div>

<style>
    .container {
        color: var(--hp-box-text);
        width: 700px;
        margin: 0 auto;
        max-width: 100%;
    }

    .header {
        padding: 20px 0;
        font-size: 1.25em;
        font-weight: var(--hp-font-weight-heading);
    }

    .issues {
        padding: 20px;
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
