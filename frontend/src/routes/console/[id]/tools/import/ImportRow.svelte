<script lang="ts">
    import { Button } from "@hyvor/design/components";
    import RelativeTime from '../../../@components/utils/RelativeTime.svelte';
    import ImportStatusBadge from './ImportStatusBadge.svelte';
    import type {Import} from "../../../types";
    import { getI18n } from '../../../lib/i18n';

    interface Props {
        importItem: Import;
        showFieldsOf: (importItem: Import) => void;
    }

    let { importItem, showFieldsOf }: Props = $props();

    const I18n = getI18n();
</script>

<div class="import-item">
    <div class="import-info">
        <div class="import-name">Import #{importItem.id}</div>
        <div class="import-date">
            <RelativeTime unix={importItem.created_at} />
        </div>
    </div>
    <div class="import-status">
        <ImportStatusBadge status={importItem.status} />
    </div>
    <div class="import-fields">
        <Button
            size="small"
            color="input"
            on:click={() => showFieldsOf(importItem)}
            disabled={importItem.status === 'requires_input'}
        >
            {I18n.t('console.tools.import.showFields')}
        </Button>
    </div>
    <div class="import-error">
        {#if importItem.status === 'failed' && importItem.error_message}
            {importItem.error_message}
        {:else if importItem.status === 'completed' && importItem.imported_subscribers}
            {I18n.t('console.tools.import.importedCount', {
                count: importItem.imported_subscribers
            })}
        {/if}
    </div>
</div>

<style>
    .import-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background-color: var(--bg-light);
        border-radius: 8px;
    }
    .import-info {
        display: flex;
        flex-direction: column;
        width: 170px;
    }
    .import-date {
        font-size: 13px;
        color: var(--text-light);
    }
    .import-status,
    .import-fields {
        width: 160px;
    }
    .import-error {
        flex: 1;
        text-align: right;
        font-size: 14px;
    }
</style>
