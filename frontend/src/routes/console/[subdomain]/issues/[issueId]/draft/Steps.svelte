<script lang="ts">
    import {Button, Tooltip, confirm, toast, Modal} from '@hyvor/design/components';
    import Step from './Step.svelte';
    import IconArrowRightShort from '@hyvor/icons/IconArrowRightShort';
    import IconArrowLeftShort from '@hyvor/icons/IconArrowLeftShort';
    import {goto} from '$app/navigation';
    import {consoleUrlWithNewsletter} from '../../../../lib/consoleUrl';
    import IconSend from '@hyvor/icons/IconSend';
    import {userApprovalStatusStore, licenseStore} from "../../../../lib/stores/consoleStore";
    import {getI18n} from '../../../../lib/i18n';
    import {draftIssueEditingStore, draftStepStore, initDraftStores} from './draftStore';
    import {sendIssue} from "../../../../lib/actions/issueActions";

    const sections = ['content', 'audience'] as const;
    const I18n = getI18n();

    let showLimitModal = $state(false);
    let currentLimit = $state(0);
    let exceedAmount = $state(0);

    function validate(): boolean {
        if (!$draftIssueEditingStore.subject || $draftIssueEditingStore.subject.trim() === '') {
            toast.error(I18n.t('console.issues.draft.sendIssue.validate.subject'));
            return false;
        }

        if ($draftIssueEditingStore.lists.length === 0) {
            toast.error(I18n.t('console.issues.draft.sendIssue.validate.lists'));
            return false;
        }

        if ($draftIssueEditingStore.content.trim() === '') {
            toast.error(I18n.t('console.issues.draft.sendIssue.validate.content'));
            return false;
        }

        if (!$licenseStore) {
            toast.error(I18n.t('console.issues.draft.sendIssue.validate.license'))
            return false;
        }

        return true;
    }

    function handleBack() {
        if ($draftStepStore === 'content') {
            goto(consoleUrlWithNewsletter('/issues'));
            return;
        }

        const currentIndex = sections.indexOf($draftStepStore);
        const previousSection = sections[currentIndex - 1];

        if (previousSection) {
            draftStepStore.set(previousSection);
        }
    }

    function handleNext() {
        const currentIndex = sections.indexOf($draftStepStore);
        const nextSection = sections[currentIndex + 1] || 'audience';
        draftStepStore.set(nextSection);
    }

    async function handleSend() {
        if (!validate()) {
            return;
        }

        const confirmed = await confirm({
            title: I18n.t('console.issues.draft.sendIssue.title'),
            content: I18n.t('console.issues.draft.sendIssue.content'),
            confirmText: I18n.t('console.issues.draft.sendIssue.confirmText'),
            autoClose: false
        });

        if (confirmed) {
            confirmed.loading();

            sendIssue($draftIssueEditingStore.id)
                .then(() => {
                    toast.success(I18n.t('console.issues.draft.sendIssue.success'));
                })
                .catch((e) => {
                    if (e.message.includes('would_exceed_limit')) {
                        currentLimit = e.data.current_limit || 0;
                        exceedAmount = e.data.exceed_amount || 0;
                        showLimitModal = true;
                    } else {
                        toast.error(I18n.t('console.issues.draft.sendIssue.failed') + ': ' + e.message);
                    }
                })
                .finally(() => {
                    confirmed.close();
                });
        }
    }
</script>

<div class="wrap">
    <div class="left">
        <Button color="input" onclick={handleBack}>
            Back
            {#snippet start()}
                <IconArrowLeftShort/>
            {/snippet}
        </Button>
    </div>
    <div class="steps">
        <Step key="content" name={I18n.t('console.issues.draft.steps.content')}/>
        <Step key="audience" name={I18n.t('console.issues.draft.steps.audience')}/>
    </div>
    <div class="right">
        {#if $draftStepStore === 'audience'}
            <Tooltip
                    text={$userApprovalStatusStore !== 'approved'
                        ? I18n.t('console.issues.draft.approveBeforeSending')
                        : I18n.t('console.issues.draft.subscriptionBeforeSending')
                    }
                    disabled={$userApprovalStatusStore === 'approved' && $licenseStore}
            >
                <Button onclick={handleSend} disabled={$userApprovalStatusStore !== 'approved' || !$licenseStore}>
                    Send Issue
                    {#snippet end()}
                        <IconSend size={14}/>
                    {/snippet}
                </Button>
            </Tooltip>
        {:else}
            <Button onclick={handleNext}>
                Next
                {#snippet end()}
                    <IconArrowRightShort/>
                {/snippet}
            </Button>
        {/if}
    </div>
</div>


<Modal
        bind:show={showLimitModal}
        title={I18n.t('console.issues.draft.sendingLimitReached.title')}
        footer={{
		cancel: {
			text: I18n.t('console.common.close')
		},
		confirm: {
			text: I18n.t('console.issues.draft.sendingLimitReached.upgrade')
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
    .wrap {
        border-top: 1px solid var(--border);
        padding: 15px 30px;
        display: flex;
        align-items: center;
    }

    .steps {
        display: flex;
        gap: 20px;
        flex: 1;
        justify-content: center;
    }

    .left,
    .right {
        width: 150px;
    }

    .right {
        text-align: right;
    }

    .right :global(.tooltip-wrap) {
        text-align: left;
    }

    .limit-error {
        padding: 20px;
        text-align: center;
        font-size: 16px;
        line-height: 1.5;
    }
</style>
