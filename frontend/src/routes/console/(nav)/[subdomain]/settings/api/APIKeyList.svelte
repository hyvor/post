<script lang="ts">
    import APIKeyRow from './APIKeyRow.svelte';
    import type {ApiKey} from '../../../types';
    import {IconMessage} from '@hyvor/design/components';
    import {getI18n} from '../../../lib/i18n';

    interface Props {
        apiKeys: ApiKey[];
        loading: boolean;
        onDelete: (apiKey: ApiKey) => void;
        onEdit: (apiKey: ApiKey) => void;
    }

    let {apiKeys, loading, onDelete, onEdit}: Props = $props();

    const I18n = getI18n();
</script>

{#if apiKeys.length === 0}
    <IconMessage empty size="large" message={I18n.t('console.settings.api.noKeys')}/>
{:else}
    <div class="api-keys-list">
        {#each apiKeys as apiKey (apiKey.id)}
            <APIKeyRow {apiKey} {onDelete} {onEdit}/>
        {/each}
    </div>
{/if}

<style>
    .api-keys-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
</style>
