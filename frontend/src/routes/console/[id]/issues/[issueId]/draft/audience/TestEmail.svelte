<script lang="ts">
    import {Button, IconButton, Modal, SplitControl, Tag, TextInput} from "@hyvor/design/components";
    import {getI18n} from "../../../../../lib/i18n";
    import IconX from "@hyvor/icons/IconX";

    const I18n = getI18n();

    let showTestEmailModal = $state(false);
    let newEmail = $state('');

    let selectedEmails: string[] = $state([]);
    let suggestedEmails: string[] = $state(['nadil@hyvor.com', 'supun@hyvor.com', 'ishini@hyvor.com', 'thibault@hyvor.com']);

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

    function selectEmail(email: string) {
        if (email.trim() === '' || selectedEmails.includes(email)) {
            return;
        }
        selectedEmails = [...selectedEmails, email];
        newEmail = '';
    }

    function deselectEmail(email: string) {
        selectedEmails = selectedEmails.filter(e => e !== email);
    }
</script>

<div class="wrap">
    {I18n.t('console.issues.draft.testEmailCaption')}
    <Button
        color="input"
        onclick={() => (showTestEmailModal = true)}
    >
        {I18n.t('console.issues.draft.testEmail')}
    </Button>
</div>

<Modal
    bind:show={showTestEmailModal}
    title={I18n.t('console.issues.draft.testEmail')}
    footer={{
        confirm: {
            text: I18n.t('console.issues.draft.testEmail')
        }
    }}
    closeOnOutsideClick={false}
    closeOnEscape={false}
>
    <SplitControl label="To">

        <div class="new-email-wrap">
            <TextInput
                bind:value={newEmail}
                placeholder="Enter email address"
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

    <SplitControl label="Suggested emails">
        <div class="suggested-emails">
            {#if suggestedEmails.every(email => selectedEmails.includes(email))}
                <div class="no-suggestions">No suggestions</div>
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
