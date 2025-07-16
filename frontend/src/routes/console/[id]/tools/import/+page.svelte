<script lang="ts">
    import {Button, IconMessage, Loader, SplitControl, toast} from '@hyvor/design/components';
	import SettingsBody from '../../settings/@components/SettingsBody.svelte';
    import ImportMapping from "./ImportMapping.svelte";
    import {uploadCsv} from "../../../lib/actions/importActions";

    let uploading = $state(false);
    let mapping = $state(false);
    let fileInput: HTMLInputElement | undefined = $state();
    let fields: string[] = $state([]);

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
                fields = res;
                mapping = true;
            })
            .catch((err) => {
                toast.error(`Error uploading file: ${err.message}`, {id: toastId});
            })
            .finally(() => {
                uploading = false;
            });
    }
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

	<SplitControl label="Import History" column>
        <IconMessage empty message="No imports found" />
	</SplitControl>

    <ImportMapping bind:show={mapping} bind:fields />
</SettingsBody>

<style>
    .upload {
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
