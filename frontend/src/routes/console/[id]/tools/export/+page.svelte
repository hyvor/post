<script lang="ts">
	import { Button, Loader, IconMessage, toast, ButtonGroup } from '@hyvor/design/components';
	import SingleBox from '../../../@components/content/SingleBox.svelte';
	import IconBoxArrowInDown from '@hyvor/icons/IconBoxArrowInDown';
	import { onMount } from 'svelte';
	import { createExport, listExports } from '../../../lib/actions/exportActions';
	import type { Export } from '../../../types';
	import ExportStatusBadge from './ExportStatusBadge.svelte';
	import RelativeTime from '../../../@components/utils/RelativeTime.svelte';
	import SettingsTop from '../../settings/@components/SettingsTop.svelte';
	import IconBoxArrowInUp from '@hyvor/icons/IconBoxArrowInUp';

	let loading = true;
	let exports: Export[] = [];
	let exporting = false;

	function loadExports() {
		loading = true;
		listExports()
			.then((data) => {
				exports = data;
			})
			.catch((e) => {
				toast.error('Failed to load exports: ' + e.message);
			})
			.finally(() => {
				loading = false;
			});
	}

	function handleExport() {
		exporting = true;
		createExport()
			.then(() => {
				toast.success('Export started');
				loadExports();
			})
			.catch((e) => {
				toast.error('Failed to start export: ' + e.message);
			})
			.finally(() => {
				exporting = false;
			});
	}

	onMount(() => {
		loadExports();
	});
</script>

<SingleBox>
    <SettingsTop>
        <ButtonGroup>
            <Button on:click={handleExport} disabled={exporting}>
                Export Subscribers
                {#snippet end()}
                    <IconBoxArrowInUp size={14} />
                {/snippet}
            </Button>
        </ButtonGroup>
    </SettingsTop>
	<div class="content">
		{#if loading}
			<Loader full />
		{:else if exports.length === 0}
			<IconMessage empty message="No exports yet" />
		{:else}
			<div class="exports">
				{#each exports as exportItem}
					<div class="export-item">
						<div class="export-info">
							<div class="export-name">Export #{exportItem.id}</div>
							<div class="export-date">
								<ExportStatusBadge status={exportItem.status} />
							</div>
							<div class="export-date">
								<RelativeTime unix={exportItem.created_at} />
							</div>
						</div>
						{#if exportItem.status === 'completed' && exportItem.url}
							<Button size="small" color="input" as="a" href={exportItem.url} target="_blank">
								Download
								{#snippet end()}
									<IconBoxArrowInUp size={12} />
								{/snippet}
							</Button>
						{/if}
					</div>
				{/each}
			</div>
		{/if}
	</div>
</SingleBox>

<style>
	.content {
		padding: 20px;
        overflow: auto;
	}

	.exports {
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.export-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 15px;
		background-color: var(--bg-light);
		border-radius: 8px;
	}

	.export-info {
		display: flex;
		flex-direction: column;
		gap: 4px;
	}

	.export-name {
		font-weight: 500;
	}

	.export-date {
		font-size: 13px;
		color: var(--text-light);
	}
</style>
