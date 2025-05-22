<script lang="ts">
	import { confirm, IconButton, TableRow, toast } from '@hyvor/design/components';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconTrash from '@hyvor/icons/IconTrash';
	import type { SubscriberMetadataDefinition } from '../../../types';
	import { getI18n } from '../../../lib/i18n';
	import { deleteSubscriberMetadataDefinition } from '../../../lib/actions/subscriberMetadataActions';
	import { subscriberMetadataDefinitionStore } from '../../../lib/stores/newsletterStore';
	import MetadataAddUpdateModal from './MetadataAddUpdateModal.svelte';

	let { metadata }: { metadata: SubscriberMetadataDefinition } = $props();

	const I18n = getI18n();

	let updating = $state(false);

	async function handleDelete() {
		const confimed = await confirm({
			title: I18n.t('console.settings.metadata.delete'),
			content: I18n.t('console.settings.metadata.deleteContent'),
			danger: true,
			autoClose: false
		});

		if (!confimed) {
			return;
		}

		confimed.loading();

		deleteSubscriberMetadataDefinition(metadata.id)
			.then(() => {
				toast.success(
					I18n.t('console.common.deleted', {
						field: I18n.t('console.settings.metadata.metadata')
					})
				);
				confimed.close();

				subscriberMetadataDefinitionStore.update((defs) => {
					return defs.filter((def) => def.id !== metadata.id);
				});
			})
			.catch((error) => {
				toast.error(error.message);
				confimed.loading(false);
			});
	}
</script>

<TableRow>
	<div>{metadata.key}</div>
	<div>{metadata.name}</div>
	<div>
		<IconButton color="input" size="small" on:click={() => (updating = true)}>
			<IconPencil size={12} />
		</IconButton>
		<IconButton color="red" variant="fill-light" size="small" on:click={handleDelete}>
			<IconTrash size={12} />
		</IconButton>
	</div>
</TableRow>

<MetadataAddUpdateModal bind:show={updating} {metadata} />
