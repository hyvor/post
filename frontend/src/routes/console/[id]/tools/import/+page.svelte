<script lang="ts">
    import {Button, IconMessage, Loader, SplitControl, CodeBlock, toast, LoadButton } from '@hyvor/design/components';
    import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import SettingsBody from '../../settings/@components/SettingsBody.svelte';
    import ImportMapping from "./ImportMapping.svelte";
    import {getImports, IMPORTS_PER_PAGE, uploadCsv} from "../../../lib/actions/importActions";
    import {onMount} from "svelte";
    import RelativeTime from "../../../@components/utils/RelativeTime.svelte";
    import type {Import} from "../../../types";
    import ImportStatusBadge from "./ImportStatusBadge.svelte";
    import {getI18n} from "../../../lib/i18n";

    let loading = $state(false);
    let hasMore = $state(false);
    let loadingMore = $state(false);
    let imports: Import[] = $state([]);

    let uploading = $state(false);
    let mapping = $state(false);
    let fileInput: HTMLInputElement | undefined = $state();
    let importId: number | undefined = $state();
    let fields: string[] = $state([]);

    const I18n = getI18n();

    function submitFile() {
        if (!fileInput) {
            return;
        }

        const files = fileInput.files;
        const importFile = files && files[0];

        if (!importFile) {
            return toast.error('Please select a file');
        }
        if (importFile.size > 100 * 1024 * 1024) {
            return toast.error('File is too large (Max 100 MB)');
        }

        uploading = true;
        const toastId = toast.loading('Uploading file...');

        uploadCsv(importFile)
            .then((res) => {
                toast.success('File uploaded successfully', {id: toastId});
                importId = res.import_id;
                fields = res.fields;
                mapping = true;
            })
            .catch((err) => {
                toast.error(`Error uploading file: ${err.message}`, {id: toastId});
            })
            .finally(() => {
                uploading = false;
            });
    }

    function loadImports(more: boolean = false) {
        more ? (loadingMore = true) : (loading = true);

        getImports(more ? imports.length : 0)
            .then((data) => {
                imports = more ? [...imports, ...data] : data;
                hasMore = data.length === IMPORTS_PER_PAGE;
            })
            .catch((e) => {
                toast.error('Failed to load imports: ' + e.message);
            })
            .finally(() => {
                loadingMore = false;
                loading = false;
            });
    }
    onMount(() => {
        loadImports();
    })
</script>

<SettingsBody>
	<SplitControl label="New Import">
		{#snippet nested()}
			<SplitControl label="Import File">
                <div class="upload">
                    <input type="file" accept=".csv" bind:this={fileInput}/>
                    <Button type="button" on:click={submitFile} disabled={uploading}>
                        Upload
                        {#snippet action()}
                            {#if uploading}
                                <Loader size="small" invert />
                            {/if}
                        {/snippet}
                    </Button>
                </div>
            </SplitControl>
		{/snippet}
	</SplitControl>

    <div class="content">
        {#if loading}
            <Loader full />
        {:else if imports.length === 0}
            <IconMessage empty message="No imports found" />
        {:else}
            <div class="imports">
                {#each imports as importItem}
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
                            <CodeBlock
                                code={JSON.stringify(importItem.fields)}
                                language="json"
                            />
                        </div>
                        <div class="import-error">
                            {#if importItem.error_message}
                                {importItem.error_message}
                            {/if}
                        </div>
                    </div>
                {/each}
            </div>
            <LoadButton
                text={I18n.t('console.common.loadMore')}
                show={hasMore}
                loading={loadingMore}
                on:click={() => loadImports(true)}
            />
        {/if}
    </div>

    {#if importId}
        <ImportMapping bind:show={mapping} importId={importId} fields={fields} />
    {/if}
</SettingsBody>

<style>
    .upload {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .content {
        padding: 20px;
        overflow: auto;
    }
    .imports {
        display: flex;
        flex-direction: column;
    }
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
    }
    .import-status {
        width: 130px;
    }
    .import-fields {
        width: 350px;
    }
    .import-date {
        font-size: 13px;
        color: var(--text-light);
    }
    .import-error {
        width: 180px;
    }
</style>
