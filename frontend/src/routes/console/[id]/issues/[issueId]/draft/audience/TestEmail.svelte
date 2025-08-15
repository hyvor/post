<script lang="ts">
    import {Button, IconButton, Modal, SplitControl, Tag, TextInput, toast} from "@hyvor/design/components";
    import {getI18n} from "../../../../../lib/i18n";
    import IconX from "@hyvor/icons/IconX";
    import {onMount} from "svelte";
    import {getIssueTestData, sendIssueTest} from "../../../../../lib/actions/issueActions";
    import {draftIssueEditingStore} from "../draftStore";

    const I18n = getI18n();

    let showTestEmailModal = $state(false);
    let newEmail = $state('');
    let isLoading = $state(true);

    let selectedEmails: string[] = $state([]);
    let suggestedEmails: string[] = $state([]);
    let verifiedDomains: string[] = $state([]);

    const newEmailActions = {
        onKeydown: (e: KeyboardEvent) => {
            if (e.key === 'Enter') {
                selectEmail(newEmail);
            }
            if (e.key === 'Escape') {
                newEmail = '';
            }
        },
        onClear: () => {
            newEmail = '';
        }
    };

    function validateEmail(email: string): boolean {
        if (email.trim() === '') {
            toast.error(I18n.t('console.issues.draft.testEmail.emailValidation.emailNonEmpty'));
            return false;
        }

        email = email.trim();

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            toast.error(I18n.t('console.issues.draft.testEmail.emailValidation.emailInvalid'));
            return false;
        }

        if (suggestedEmails.includes(email)) {
            return true;
        }

        const domain = email.split('@')[1];
        if (!verifiedDomains.includes(domain)) {
            toast.error(I18n.t('console.issues.draft.testEmail.emailValidation.emailNotAllowed'));
            return false;
        }
        return true;
    }

    function selectEmail(email: string) {
        if (!validateEmail(email)) {
            return;
        }
        selectedEmails = [...selectedEmails, email];
        newEmail = '';
    }

    function deselectEmail(email: string) {
        selectedEmails = selectedEmails.filter(e => e !== email);
    }

    function handleConfirm() {
        const toastId = toast.loading(I18n.t('console.issues.draft.testEmail.confirm.sending'));

        sendIssueTest($draftIssueEditingStore.id, selectedEmails)
            .then((res) => {
                let count = res.success_count;
                if (count > 1) {
                    toast.success(I18n.t('console.issues.draft.testEmail.confirm.sentMultiple', {count: count}), {id: toastId});
                } else if (count === 1) {
                    toast.success(I18n.t('console.issues.draft.testEmail.confirm.sent'), {id: toastId});
                } else {
                    toast.error(I18n.t('console.issues.draft.testEmail.confirm.failed'), {id: toastId});
                }
            })
            .catch((e) => {
                toast.error(I18n.t('console.issues.draft.testEmail.confirm.failed') + ': ' + e.message, {id: toastId});
            });
    }

    onMount(() => {
        getIssueTestData($draftIssueEditingStore.id)
            .then((res) => {
                suggestedEmails = res.suggested_emails;
                selectedEmails = res.test_sent_emails;
                verifiedDomains = res.verified_domains;
                isLoading = false;
            });
    });
</script>

<div class="wrap">
    {I18n.t('console.issues.draft.testEmail.description')}
    <Button
        color="input"
        onclick={() => (showTestEmailModal = true)}
    >
        {I18n.t('console.issues.draft.testEmail.title')}
    </Button>
</div>

<Modal
    bind:show={showTestEmailModal}
    loading={isLoading}
    title={I18n.t('console.issues.draft.testEmail.title')}
    footer={{
        confirm: {
            text: I18n.t('console.issues.draft.testEmail.title'),
            props: {
                disabled: selectedEmails.length === 0
            }
        }
    }}
    on:confirm={handleConfirm}
    closeOnOutsideClick={false}
    closeOnEscape={false}
>
    <SplitControl label={I18n.t('console.issues.draft.testEmail.modal.to')}>
        <div class="new-email-wrap">
            <TextInput
                bind:value={newEmail}
                placeholder={I18n.t('console.issues.draft.testEmail.modal.toPlaceholder')}
                on:keydown={newEmailActions.onKeydown}
                type="email"
                size="small"
                block
            >
                {#snippet end()}
                    {#if newEmail.trim() !== ''}
                        <IconButton
                            variant="invisible"
                            color="gray"
                            size={16}
                            on:click={newEmailActions.onClear}
                        >
                            <IconX size={12}/>
                        </IconButton>
                    {/if}
                {/snippet}
            </TextInput>

            {#if newEmail.trim() !== ''}
                <span class="press-enter"> ⏎ </span>
            {/if}
        </div>

        <div class="selected-emails">
            {#each selectedEmails as selectedEmail (selectedEmail)}
                <Tag size="small">
                    {selectedEmail}
                    {#snippet end()}
                        <IconButton
                            variant="fill"
                            color="gray"
                            size={12}
                            onclick={() => deselectEmail(selectedEmail)}
                        >
                            <IconX size={10}/>
                        </IconButton>
                    {/snippet}
                </Tag>
            {/each}
        </div>

    </SplitControl>

    <SplitControl
        label={I18n.t('console.issues.draft.testEmail.modal.suggested')}
        caption={I18n.t('console.issues.draft.testEmail.modal.suggestedCaption')}
    >
        <div class="suggested-emails">
            {#if suggestedEmails.every(email => selectedEmails.includes(email))}
                <div class="no-suggestions">{I18n.t('console.issues.draft.testEmail.modal.noSuggested')}</div>
            {:else}
                {#each suggestedEmails as suggestedEmail (suggestedEmail)}
                    {#if !selectedEmails.includes(suggestedEmail)}
                        <Button
                            size="x-small"
                            variant="outline-fill"
                            color="input"
                            onclick={() => selectEmail(suggestedEmail)}
                        >
                            {suggestedEmail}
                        </Button>
                    {/if}
                {/each}
            {/if}
        </div>
    </SplitControl>
</Modal>

<style>
    .wrap {
        width: 100%;
        border-top: 1px solid var(--border);
        padding: 15px 30px;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .new-email-wrap {
        display: flex;
        width: 100%;
        align-items: center;
        margin-bottom: 10px;
        gap: 5px;

        .press-enter {
            color: var(--text-light);
            font-size: 14px;
            margin-top: 4px;
            margin-left: 4px;
        }

        :global(input) {
            font-size: 14px;
        }
    }

    .selected-emails {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .suggested-emails {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .no-suggestions {
        font-size: 14px;
        color: var(--text-light);
    }
</style>
