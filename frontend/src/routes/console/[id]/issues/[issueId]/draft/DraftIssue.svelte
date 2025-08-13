<script lang="ts">
    import {
        Button,
        SplitControl,
        TextInput,
        confirm,
        toast,
        Modal
    } from '@hyvor/design/components';
    import type {Issue} from '../../../../types';
    import {sendIssue, sendIssueTest} from '../../../../lib/actions/issueActions';
    import Preview from './Preview.svelte';
    import IconSend from '@hyvor/icons/IconSend';
    import {onMount} from 'svelte';
    import {getI18n} from '../../../../lib/i18n';
    import {draftIssueEditingStore, draftStepStore, initDraftStores} from './draftStore';
    import ContentView from './content/ContentView.svelte';
    import Steps from './Steps.svelte';
    import Audience from './audience/Audience.svelte';

    interface Props {
        issue: Issue;
        send: (e: Issue) => void;
    }

    let {issue, send}: Props = $props();

    let scrollTopEl = $state({} as HTMLDivElement);
    let testEmail = $state('');
    let showLimitModal = $state(false);
    let currentLimit = $state(0);
    let exceedAmount = $state(0);
    let subjectError = '';
    let selectedSegmentsError = '';
    let subject = '';
    let selectedLists = [];

    onMount(() => {
        return;
    });

    function validate(): boolean {
        subjectError = '';
        selectedSegmentsError = '';

        let ret = true;

        function hasError() {
            ret = false;
            scrollTopEl.scrollIntoView({behavior: 'smooth'});
        }

        if (!$draftIssueEditingStore.subject || $draftIssueEditingStore.subject.trim() === '') {
            subjectError = 'Subject is required';
            hasError();
        }

        if ($draftIssueEditingStore.lists.length === 0) {
            selectedSegmentsError = 'At least one segment is required';
            hasError();
        }

        return ret;
    }

    const I18n = getI18n();
    let init = $state(false);

    onMount(() => {
        initDraftStores(issue);
        init = true;
    });

    async function onSend() {
        if (!validate()) {
            return;
        }

        const confirmed = await confirm({
            title: 'Final Confirmation',
            content:
                'You are about to send this newsletter issue. This is the final step. Are you sure you want to send this? Please double-check everything before sending as you cannot undo this action.',
            confirmText: 'Yes, send it',
            cancelText: 'Cancel'
        });

        if (confirmed) {
            confirmed.loading();

            sendIssue(issue.id)
                .then((res) => {
                    toast.success('Newsletter sent successfully');
                    send(res);
                })
                .catch((e) => {
                    if (e.message.includes('would_exceed_limit')) {
                        currentLimit = e.data.current_limit || 0;
                        exceedAmount = e.data.exceed_amount || 0;
                        showLimitModal = true;
                    } else {
                        toast.error('Failed to send newsletter: ' + e.message);
                    }
                })
                .finally(() => {
                    confirmed.close();
                });
        }
    }

    function onTestSend() {
        const toastId = toast.loading('Sending test email...');

        sendIssueTest(issue.id, testEmail)
            .then((res) => {
                toast.success('Test email sent successfully', {id: toastId});
            })
            .catch((e) => {
                toast.error('Failed to send test email: ' + e.message, {id: toastId});
            });
    }
</script>

<div bind:this={scrollTopEl}></div>

{#if init}
    <div class="draft-wrap">
        {#if $draftStepStore === 'content'}
            <ContentView/>
        {:else if $draftStepStore === 'audience'}
            <Audience/>
        {/if}

        <Steps/>
    </div>
    <!--

    <SplitControl label="Emails">
        {#snippet nested()}
            <FromEmail />
            <FromName />
            <ReplyToEmail />
        {/snippet}
    </SplitControl>

    <Content />
    <Preview />

    <SplitControl label="Send Test Email">
        <div class="send-test">
            <TextInput bind:value={testEmail} block placeholder="Email address" />
            <Button on:click={onTestSend}>Send Test Email</Button>
        </div>
    </SplitControl>

    <div class="send">
        <div class="ready">Ready to send?</div>
        <Button size="large" on:click={onSend}>
            Send Now
            {#snippet end()}
                <IconSend />
            {/snippet}
        </Button>
    </div> -->
{/if}

<Modal
    bind:show={showLimitModal}
    title={I18n.t('console.issues.draft.sendingLimitReached.title')}
    footer={{
		cancel: {
			text: 'Close'
		},
		confirm: {
			text: 'Upgrade'
		}
	}}
    on:cancel={() => (showLimitModal = false)}
    on:confirm={() => {
		showLimitModal = false;
		window.location.href = '/console/billing';
	}}
>
    <p class="limit-error">
        {I18n.t('console.issues.draft.sendingLimitReached.message', {
            currentLimit,
            exceedAmount
        })}
    </p>
</Modal>

<style>
    .draft-wrap {
        display: flex;
        flex-direction: column;
        flex: 1;
        height: 100%;
        overflow: hidden;
    }

    .send {
        padding: 30px;
        text-align: center;
    }

    .send .ready {
        font-size: 20px;
        margin-bottom: 10px;
    }

    .send-test {
        display: flex;
        gap: 6px;
    }

    .send-test :global(button) {
        flex-shrink: 0;
    }

    .limit-error {
        padding: 20px;
        text-align: center;
        font-size: 16px;
        line-height: 1.5;
    }

    @media (max-width: 992px) {
        .send-test {
            flex-direction: column;
        }
    }
</style>
