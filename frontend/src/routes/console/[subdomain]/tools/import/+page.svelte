<script lang="ts">
    import {
        Button,
        IconMessage,
        Loader,
        SplitControl,
        toast,
        LoadButton,
        Tooltip
        ,

    } from '@hyvor/design/components';
    import SettingsBody from '../../settings/@components/SettingsBody.svelte';
    import ImportMapping from './ImportMapping.svelte';
    import {getImportLimits, getImports, IMPORTS_PER_PAGE, uploadCsv} from '../../../lib/actions/importActions';
    import {onMount} from 'svelte';
    import type {Import, ImportLimits} from '../../../types';
    import {getI18n} from '../../../lib/i18n';
    import ImportFieldMapModal from './ImportFieldMapModal.svelte';
    import WarningsModal from './WarningsModal.svelte';
    import ImportRow from "./ImportRow.svelte";
    import {importStore} from '../../../lib/stores/newsletterStore';

    let loading = $state(false);
    let hasMore = $state(false);
    let loadingMore = $state(false);

    let importLimitsOfNewsletter: ImportLimits = $state({
        daily_limit_exceeded: false,
        monthly_limit_exceeded: false
    });
    let limitsExceeded = $state(false);

    let uploading = $state(false);
    let mapping = $state(false);
    let fileInput: HTMLInputElement | undefined = $state();
    let importId: number | undefined = $state();
    let fields: string[] = $state([]);

    let showFields = $state(false);
    let fieldMap = $state<Record<string, string | null>>({});

    let showWarnings = $state(false);
    let warnings: string | null = $state(null);

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
            .then((data) => {
                toast.info('File uploaded, map the fields to start the import', {id: toastId});
                importStore.update(imports => {
                    const index = imports.findIndex(i => i.id === data.id);
                    if (index !== -1) {
                        imports[index] = {...imports[index], ...data};
                    } else {
                        imports = [data, ...imports];
                    }
                    return imports;
                });
                showFieldMappingModalOf(data)
            })
            .catch((err) => {
                toast.error(err.message, {id: toastId});
            })
            .finally(() => {
                uploading = false;
            });
    }

    function loadImportLimits() {
        getImportLimits()
            .then((limits) => {
                importLimitsOfNewsletter = limits;
            })
            .catch((e) => {
                toast.error('Failed to load import limits: ' + e.message);
            });
    }

    function loadImports(more: boolean = false) {
        more ? (loadingMore = true) : (loading = true);

        getImports(more ? $importStore.length : 0)
            .then((data) => {
                more
                    ? importStore.update(imports => [...imports, ...data])
                    : importStore.set(data);
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

    function showFieldsOf(importItem: Import) {
        fieldMap = importItem.fields || {};
        showFields = true;
    }

    function showFieldMappingModalOf(importItem: Import) {
        importId = importItem.id;
        fields = importItem.csv_fields || [];
        mapping = true;
    }

    function showWarningsOf(importItem: Import) {
        warnings = importItem.warnings;
        showWarnings = true;
    }

    $effect(() => {
        limitsExceeded = importLimitsOfNewsletter.monthly_limit_exceeded || importLimitsOfNewsletter.daily_limit_exceeded;
    })

    onMount(() => {
        loadImportLimits();
        loadImports();
    });
</script>

<SettingsBody>
    <SplitControl label="New Import">
        {#snippet nested()}
            <SplitControl label="Import File">
                <div class="upload">
                    <input type="file" accept=".csv" bind:this={fileInput}/>
                    <Tooltip
                            text={
							importLimitsOfNewsletter.monthly_limit_exceeded ? 'Monthly import limit exceeded. (5/5)' :
							importLimitsOfNewsletter.daily_limit_exceeded ? 'Daily import limit exceeded. (1/1)' : undefined
						}
                            disabled={!limitsExceeded}
                    >
                        <Button
                                type="button"
                                on:click={submitFile}
                                disabled={
								importLimitsOfNewsletter.monthly_limit_exceeded
								|| importLimitsOfNewsletter.daily_limit_exceeded
								|| uploading
							}>
                            Upload
                            {#snippet action()}
                                {#if uploading}
                                    <Loader size="small" invert/>
                                {/if}
                            {/snippet}
                        </Button>
                    </Tooltip>
                </div>
            </SplitControl>
        {/snippet}
    </SplitControl>

    <div class="content">
        {#if loading}
            <Loader full/>
        {:else if $importStore.length === 0}
            <IconMessage empty message="No imports found"/>
        {:else}
            <div class="imports">
                {#each $importStore as importItem (importItem.id)}
                    <ImportRow {importItem} {showFieldsOf} {showFieldMappingModalOf} {showWarningsOf}
                               importDisabled={limitsExceeded}/>
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
        <ImportMapping bind:show={mapping} {importId} {fields}/>
    {/if}

    <ImportFieldMapModal bind:show={showFields} bind:fieldMap/>
    <WarningsModal bind:show={showWarnings} bind:warnings/>
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
</style>
