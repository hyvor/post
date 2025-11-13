<script lang="ts">
    import {
        Button,
        Modal,
        TextInput,
        SplitControl,
        toast,
        confirm,
        IconButton,
        Tag,
        Loader
    } from '@hyvor/design/components';
    import IconPlus from '@hyvor/icons/IconPlus';
    import IconCopy from '@hyvor/icons/IconCopy';
    import SingleBox from '../../../../@components/content/SingleBox.svelte';
    import CreateApiKeyModal from './ApiKeyModal.svelte';
    import APIKeyList from './APIKeyList.svelte';
    import type {ApiKey} from '../../../../types';
    import {getApiKeys, deleteApiKey} from '../../../../lib/actions/apiKeyActions';
    import {copyAndToast} from "$lib/helpers/copy";
    import {onMount} from 'svelte';
    import {getI18n} from '../../../../lib/i18n';

    let apiKeys: ApiKey[] = $state([]);
    let loading = $state(true);
    let showCreateModal = $state(false);
    let showApiKeyModal = $state(false);
    let newApiKey: ApiKey | null = $state(null);
    let editingApiKey: ApiKey | null = $state(null);

    const I18n = getI18n();

    onMount(() => {
        loadApiKeys();
    });

    function loadApiKeys() {
        loading = true;
        getApiKeys()
            .then((keys) => {
                apiKeys = keys;
            })
            .catch((error) => {
                console.error(I18n.t('console.common.failedToLoadField', {field: I18n.t('console.settings.api.apiKey')}) + ':', error);
                toast.error(I18n.t('console.common.failedToLoadField'));
            })
            .finally(() => {
                loading = false;
            });
    }

    function handleApiKeyCreated(apiKey: ApiKey) {
        newApiKey = apiKey;
        showApiKeyModal = true;
        loadApiKeys();
    }

    function handleApiKeyUpdated(apiKey: ApiKey) {
        editingApiKey = null;
        loadApiKeys();
    }

    function handleEditApiKey(apiKey: ApiKey) {
        editingApiKey = apiKey;
        showCreateModal = true;
    }

    async function handleDeleteApiKey(apiKey: ApiKey) {
        const confirmed = await confirm({
            title: I18n.t('console.settings.api.apiKey'),
            content: I18n.t('console.settings.api.deleteKey', {name: apiKey.name}),
            confirmText: I18n.t('console.common.delete'),
            cancelText: I18n.t('console.common.cancel'),
            danger: true
        });

        if (confirmed) {
            deleteApiKey(apiKey.id)
                .then(() => {
                    loadApiKeys();
                    toast.success(I18n.t('console.common.deleted', {field: I18n.t('console.settings.api.apiKey')}));
                })
                .catch((error) => {
                    console.error(I18n.t('console.common.failedToLoadField', {field: I18n.t('console.settings.api.apiKey')}) + ':', error);
                    toast.error(I18n.t('console.common.failedToLoadField', {field: I18n.t('console.settings.api.apiKey')}));
                });
        }
    }

    // Watch for modal close to reset editing state
    $effect(() => {
        if (!showCreateModal) {
            editingApiKey = null;
        }
    });
</script>

<SingleBox>
    <div class="top">
        <Button variant="fill" on:click={() => (showCreateModal = true)}>
            <IconPlus size={16}/>
            {I18n.t('console.common.createField', {field: I18n.t('console.settings.api.apiKey')})}
        </Button>
    </div>

    <div class="content">
        {#if loading}
            <div class="loader-container">
                <Loader/>
            </div>
        {:else}
            <APIKeyList
                    {apiKeys}
                    loading={false}
                    onDelete={handleDeleteApiKey}
                    onEdit={handleEditApiKey}
            />
        {/if}
    </div>
</SingleBox>

<CreateApiKeyModal
        bind:show={showCreateModal}
        {editingApiKey}
        onApiKeyCreated={handleApiKeyCreated}
        onApiKeyUpdated={handleApiKeyUpdated}
/>

<!-- Show New API Key Modal -->
{#if showApiKeyModal && newApiKey}
    <Modal
            title={I18n.t('console.settings.api.newKey')}
            bind:show={showApiKeyModal}
            size="medium"
            footer={{
                cancel: {
                    text: I18n.t('console.common.close')
                },
                confirm: false
            }}
            closeOnOutsideClick={false}
    >
        <div class="modal-content">
            <div class="warning-box">
                <I18n.T key="console.settings.api.warningNotice" params={{
                    strong: { element: 'strong' }
                }}/>
            </div>

            <SplitControl label={I18n.t('console.settings.api.apiKey')}>
                <div class="key-input-group">
                    <TextInput value={newApiKey.key || ''} readonly block/>
                    <IconButton
                            size="small"
                            color="input"
                            style="margin-left:4px;"
                            on:click={() => copyAndToast(newApiKey?.key || '', I18n.t('console.common.copied', { value: I18n.t('console.settings.api.apiKey') }))}
                    >
                        <IconCopy size={12}/>
                    </IconButton>
                </div>
            </SplitControl>

            <SplitControl label={I18n.t('console.settings.api.name')}>
                <span>{newApiKey.name}</span>
            </SplitControl>

            <SplitControl label={I18n.t('console.settings.api.scopes')}>
                <div class="scopes-display">
                    {#each newApiKey.scopes as scope}
                        <Tag size="small">
                            {scope}
                        </Tag>
                    {/each}
                </div>
            </SplitControl>
        </div>
    </Modal>
{/if}

<style>
    .top {
        display: flex;
        padding: 20px 30px;
        border-bottom: 1px solid var(--border);
    }

    .content {
        padding: 30px;
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: auto;
    }

    .loader-container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex: 1;
    }

    .modal-content {
        padding: 20px 0;
    }

    .warning-box {
        padding: 16px;
        background: var(--orange-50);
        border: 1px solid var(--orange-200);
        border-radius: 6px;
        color: var(--orange-900);
        margin-bottom: 20px;
    }

    .key-input-group {
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }

    .scopes-display {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
</style>
