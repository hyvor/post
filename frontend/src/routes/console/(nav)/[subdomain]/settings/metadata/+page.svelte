<script>
	import { Button, IconMessage, Table, TableRow } from '@hyvor/design/components';
	import TopBar from '../../../../@components/content/TopBar.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import { subscriberMetadataDefinitionStore } from '../../../../lib/stores/newsletterStore.ts';
	import SettingsBody from '../@components/SettingsBody.svelte';
	import MetadataRow from './MetadataRow.svelte';
	import { getI18n } from '../../../../lib/i18n.ts';
	import AddUpdateModal from './MetadataAddUpdateModal.svelte';

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
				<div>{I18n.t('console.settings.metadata.key')}</div>
				<div>{I18n.t('console.settings.metadata.displayName')}</div>
			</TableRow>

			{#each $subscriberMetadataDefinitionStore as metadata}
				<MetadataRow {metadata} />
			{/each}
		</Table>
	{:else}
		<IconMessage empty message={I18n.t('console.settings.metadata.notFound')} padding={200} />
	{/if}
</SettingsBody>

{#if adderOpen}
	<AddUpdateModal bind:show={adderOpen} />
{/if}
