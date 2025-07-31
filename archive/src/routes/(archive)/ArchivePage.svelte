<script>
    import {issuesStore, newsletterStore} from '$lib/archiveStore';
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
                    {issue.sent_at}
                </div>
            </a>
        {/each}
    </div>
</div>

<style>
    .container {
        width: 700px;
        margin: 0 auto;
        max-width: 100%;
    }

    .header {
        padding: 20px 0;
        font-size: 20px;
        font-weight: 600;
    }

    .issue {
        display: block;
    }
</style>
