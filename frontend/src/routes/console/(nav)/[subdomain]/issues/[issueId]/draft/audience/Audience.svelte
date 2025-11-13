<script>
    import Lists from './Lists.svelte';
    import SendingProfile from './SendingProfile.svelte';
    import TestEmail from "./TestEmail.svelte";
    import {Button, Callout} from "@hyvor/design/components";
    import IconExclamationCircle from "@hyvor/icons/IconExclamationCircle";
    import {newsletterLicenseStore} from "../../../../../lib/stores/newsletterStore.js";
    import {userApprovalStatusStore} from "../../../../../lib/stores/consoleStore.js";
</script>

<div class="audience-wrap">

    <div class="audience">
        <Lists/>
        <SendingProfile/>
    </div>

    <div class="test-email">

        {#if $userApprovalStatusStore !== 'approved' || !$newsletterLicenseStore}
            <div class="subscription-callout">
                <Callout type="warning">
                    {#snippet icon()}
                        <IconExclamationCircle/>
                    {/snippet}

                    {#if $userApprovalStatusStore !== 'approved'}
                        <div class="message">
                            Your account must be approved to send newsletter issues.
                            <Button size="small" as="a" href="/console/approve">Go to Approval</Button>
                        </div>
                    {:else}
                        <div class="message">
                            You must have an active subscription to send newsletter issues.
                            <Button size="small" as="a" href="/console/billing">Go to Billing</Button>
                        </div>
                    {/if}
                </Callout>
            </div>
        {/if}

        <TestEmail/>
    </div>
</div>

<style>
    .audience-wrap {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .audience {
        padding: 30px 50px;
    }

    .test-email {
        margin-top: auto;
    }

    .subscription-callout {
        padding: 15px 30px;
    }

    .message {
        display: flex;
        align-items: center;
        justify-items: flex-end;
        gap: 8px;
    }

    :global(.text-wrap) {
        align-items: center !important;
    }
</style>
