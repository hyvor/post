<script lang="ts">
    import {
        Modal,
        TextInput,
        SplitControl,
        toast,
        Checkbox,
        Switch,
        Button,
        confirm
    } from '@hyvor/design/components';
    import IconArrowCounterclockwise from '@hyvor/icons/IconArrowCounterclockwise';
    import {createApiKey, regenerateApiKey, updateApiKey} from '../../../lib/actions/apiKeyActions';
    import type {ApiKey} from '../../../types';
    import {getAppConfig} from "../../../lib/stores/consoleStore";
    import {getI18n} from '../../../lib/i18n';

    interface Props {
        show: boolean;
        editingApiKey?: ApiKey | null;
        onApiKeyCreated?: (apiKey: ApiKey) => void;
        onApiKeyUpdated?: (apiKey: ApiKey) => void;
    }

    let {
        show = $bindable(),
        editingApiKey = null,
        onApiKeyCreated = () => {
        },
        onApiKeyUpdated = () => {
        }
    }: Props = $props();

    let name = $state('');
    let selectedScopes = $state<string[]>([]);
    let isEnabled = $state(true);
    let loading = $state(false);
    let errors = $state<Record<string, string>>({});

    const appConfig = getAppConfig();
    const scopes = appConfig.app?.api_keys?.scopes || [];
    const I18n = getI18n();

    // Watch for editingApiKey changes to populate form
    $effect(() => {
        if (editingApiKey) {
            name = editingApiKey.name;
            selectedScopes = [...editingApiKey.scopes];
            isEnabled = editingApiKey.is_enabled;
        } else {
            resetForm();
        }
    });

    function resetForm() {
        name = '';
        selectedScopes = [];
        isEnabled = true;
        errors = {};
    }

    function validateForm(): boolean {
        errors = {};

        if (!name.trim()) {
            errors.name = I18n.t('console.settings.api.nameRequired');
        } else if (name.trim().length > 255) {
            errors.name = I18n.t('console.settings.api.nameCharLimit');
        }

        if (selectedScopes.length === 0) {
            errors.scopes = I18n.t('console.settings.api.scopesRequired');
        }

        return Object.keys(errors).length === 0;
    }

    async function handleRegenerate() {

        if (!editingApiKey) {
            return;
        }

        const confirmation = await confirm({
            title: I18n.t('console.settings.api.regenerateTitle'),
            content: I18n.t('console.settings.api.regenerateContent'),
            confirmText: I18n.t('console.settings.api.regenerate'),
            cancelText: I18n.t('console.common.cancel'),
            danger: true,
            autoClose: false
        });

        if (confirmation) {
            confirmation.loading(I18n.t('console.settings.api.regenerating'))
            await regenerateApiKey(editingApiKey.id)
                .then(() => {
                    toast.success(I18n.t('console.settings.api.regenerateSuccess'));
                })
                .catch(() => {
                    toast.error(I18n.t('console.settings.api.regenerateFailed'));
                })
                .finally(() => {
                    confirmation.close();
                });
        }
    }

    function handleSubmit() {
        if (!validateForm()) {
            return;
        }

        loading = true;

        const promise = editingApiKey
            ? updateApiKey(editingApiKey.id, {
                name: name.trim(),
                scopes: selectedScopes,
                is_enabled: isEnabled
            })
            : createApiKey(name.trim(), selectedScopes);

        promise
            .then((apiKey) => {
                if (editingApiKey) {
                    onApiKeyUpdated(apiKey);
                    toast.success(I18n.t('console.common.updated', {field: I18n.t('console.settings.api.apiKey')}));
                } else {
                    onApiKeyCreated(apiKey);
                    toast.success(I18n.t('console.common.created', {field: I18n.t('console.settings.api.apiKey')}));
                }
                show = false;
                resetForm();
            })
            .catch((error) => {
                console.error(I18n.t(
                    'console.settings.api.failedAction', {
                        field: editingApiKey ? I18n.t('console.settings.api.update') : I18n.t('console.settings.api.create')
                    }
                ), error);
                toast.error(I18n.t(
                    'console.settings.api.failedAction', {
                        field: editingApiKey ? I18n.t('console.settings.api.update') : I18n.t('console.settings.api.create')
                    }
                ));
            })
            .finally(() => {
                loading = false;
            });
    }

    function handleClose() {
        show = false;
        resetForm();
    }

    function handleScopeToggle(scopeValue: string) {
        if (selectedScopes.includes(scopeValue)) {
            selectedScopes = selectedScopes.filter((s) => s !== scopeValue);
        } else {
            selectedScopes = [...selectedScopes, scopeValue];
        }
    }

    const isEditing = $derived(!!editingApiKey);
    const modalTitle = $derived(isEditing ? I18n.t('console.common.editField', {field: I18n.t('console.settings.api.apiKey')}) : I18n.t('console.common.createField', {field: I18n.t('console.settings.api.apiKey')}));
    const confirmText = $derived(isEditing ? I18n.t('console.common.updateField', {field: I18n.t('console.settings.api.apiKey')}) : I18n.t('console.common.createField', {field: I18n.t('console.settings.api.apiKey')}));
</script>

<Modal
    bind:show
    {loading}
    size="medium"
    footer={{
		cancel: {
			text: I18n.t('console.common.cancel')
		},
		confirm: {
			text: confirmText
		}
	}}
    title={modalTitle}
    on:cancel={handleClose}
    on:confirm={handleSubmit}
    closeOnOutsideClick={false}
>
    <div class="modal-content">
        <SplitControl
            label={I18n.t('console.settings.api.name')}
            caption={I18n.t('console.settings.api.nameCaption')}
            error={errors.name}
        >
            <TextInput
                bind:value={name}
                placeholder={I18n.t('console.settings.api.namePlaceholder')}
                block
                disabled={loading}
            />
        </SplitControl>

        {#if isEditing}
            <SplitControl
                label={I18n.t('console.settings.api.key')}
                caption={I18n.t('console.settings.api.keyCaption')}
            >
                <Button
                    color="gray"
                    variant="outline"
                    size="small"
                    onclick={() => handleRegenerate()}
                >
                    {I18n.t('console.settings.api.regenerate')}

                    {#snippet end()}
                        <IconArrowCounterclockwise/>
                    {/snippet}
                </Button>
            </SplitControl>
        {/if}

        <SplitControl
            label={I18n.t('console.settings.api.scopes')}
            caption={I18n.t('console.settings.api.scopesCaption')}
            error={errors.scopes}
        >
            <div class="scopes-header">
                <div class="scopes-actions">
                    <button
                        type="button"
                        class="scope-action-btn"
                        disabled={loading || selectedScopes.length === scopes.length}
                        onclick={() => (selectedScopes = [...scopes])}
                    >
                        {I18n.t('console.common.selectAll')}
                    </button>
                    <button
                        type="button"
                        class="scope-action-btn"
                        disabled={loading || selectedScopes.length === 0}
                        onclick={() => (selectedScopes = [])}
                    >
                        {I18n.t('console.common.deselectAll')}
                    </button>
                </div>
            </div>
            <div class="scopes-container">
                {#each scopes as scope}
                    <div class="scope-item">
                        <Checkbox
                            checked={selectedScopes.includes(scope)}
                            disabled={loading}
                            on:change={() => handleScopeToggle(scope)}
                        >
                            <div class="scope-content">
                                <span class="scope-name">{scope}</span>
                            </div>
                        </Checkbox>
                    </div>
                {/each}
            </div>
        </SplitControl>

        {#if isEditing}
            <SplitControl label={I18n.t('console.settings.api.status')}
                          caption={I18n.t('console.settings.api.statusCaption')}>
                <Switch bind:checked={isEnabled} disabled={loading}>
                    {isEnabled ? I18n.t('console.settings.api.enabled') : I18n.t('console.settings.api.disabled') }
                </Switch>
            </SplitControl>
        {/if}
    </div>
</Modal>

<style>
    .modal-content {
        padding: 20px 0;
        max-height: 70vh;
        overflow-y: auto;
    }

    .scopes-header {
        margin-bottom: 12px;
    }

    .scopes-actions {
        display: flex;
        gap: 16px;
    }

    .scope-action-btn {
        background: none;
        border: none;
        color: var(--primary);
        cursor: pointer;
        font-size: 14px;
        padding: 0;
        text-decoration: underline;
        transition: color 0.2s;
    }

    .scope-action-btn:hover:not(:disabled) {
        color: var(--primary-dark);
    }

    .scope-action-btn:disabled {
        color: var(--text-light);
        cursor: not-allowed;
        text-decoration: none;
    }

    .scopes-container {
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 15px;
    }

    .scope-item {
        display: flex;
        align-items: center;
    }

    .scope-content {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 8px;
    }

    .scope-name {
        font-weight: 500;
    }
</style>
