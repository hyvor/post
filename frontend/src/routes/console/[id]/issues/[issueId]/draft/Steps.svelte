<script lang="ts">
    import {Button, Tooltip, confirm, toast} from '@hyvor/design/components';
    import Step from './Step.svelte';
    import IconArrowRightShort from '@hyvor/icons/IconArrowRightShort';
    import IconArrowLeftShort from '@hyvor/icons/IconArrowLeftShort';
    import {draftStepStore} from './draftStore';
    import {goto} from '$app/navigation';
    import {consoleUrlWithNewsletter} from '../../../../lib/consoleUrl';
    import IconSend from '@hyvor/icons/IconSend';
    import {userApprovalStatusStore} from "../../../../lib/stores/consoleStore";
    import {getI18n} from '../../../../lib/i18n';

    const sections = ['content', 'audience'] as const;
    const I18n = getI18n();

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
        const confirmed = await confirm({
            title: I18n.t('console.issues.draft.sendIssue.title'),
            content: I18n.t('console.issues.draft.sendIssue.content'),
            confirmText: I18n.t('console.issues.draft.sendIssue.confirmText'),
            autoClose: false
        });

        if (confirmed) {
            confirmed.close();
            // TODO: backend action goes here
            toast.success(I18n.t('console.issues.draft.sendIssue.success'));
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
        <Step key="content" name="Content"/>
        <Step key="audience" name="Audience & Send"/>
    </div>
    <div class="right">
        {#if $draftStepStore === 'audience'}
            <Tooltip
                text={I18n.t('console.issues.draft.approveBeforeSending')}
                disabled={$userApprovalStatusStore === 'approved'}
            >
                <Button onclick={handleSend} disabled={$userApprovalStatusStore !== 'approved'}>
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
</style>
