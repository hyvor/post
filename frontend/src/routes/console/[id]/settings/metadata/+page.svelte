<script>
	import { Button, Table, TableRow } from '@hyvor/design/components';
	import TopBar from '../../../@components/content/TopBar.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import { subscriberMetadataDefinitionStore } from '../../../lib/stores/projectStore';
	import IconMessage from '../../../../../design/dist/components/IconMessage/IconMessage.svelte';
	import SettingsBody from '../@components/SettingsBody.svelte';
	import MetadataRow from './MetadataRow.svelte';
	import { getI18n } from '../../../lib/i18n';

	const I18n = getI18n();

	let adderOpen = $state(false);
</script>

<TopBar>
	<Button on:click={() => (adderOpen = true)}>
		{I18n.t('console.settings.metadata.add')}
		{#snippet end()}
			<IconPlus />
		{/snippet}
	</Button>
</TopBar>

<SettingsBody>
	{#if $subscriberMetadataDefinitionStore.length}
		<Table columns="1fr 1fr 100px">
			<TableRow head>
				<div>Key</div>
				<div>Name</div>
			</TableRow>

			{#each $subscriberMetadataDefinitionStore as metadata}
				<MetadataRow {metadata} />
			{/each}
		</Table>
	{:else}
		<IconMessage empty message="No metadata found" />
	{/if}
</SettingsBody>
