<script lang="ts">
	import { FormControl, Modal, SplitControl, TextInput, toast } from '@hyvor/design/components';
	import {
		selectedSubscriberIdsStore,
		subscriberMetadataDefinitionStore
	} from '../../../lib/stores/newsletterStore';
	import { updateSubscribersMetadata } from '../../../lib/actions/subscriberActions';
	import { getI18n } from '../../../lib/i18n';

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let loading = $state(false);
	let metadata = $state<Record<string, string>>({});

	const I18n = getI18n();

	function handleConfirm() {
		loading = true;
		updateSubscribersMetadata($selectedSubscriberIdsStore, metadata)
			.then(() => {
				toast.success(I18n.t('console.subscribers.bulk.metadataUpdateSuccess'));
				show = false;
				selectedSubscriberIdsStore.set([]);
			})
			.catch((error: unknown) => {
				if (error instanceof Error) {
					toast.error(error.message);
				} else {
					toast.error(I18n.t('console.subscribers.bulk.metadataUpdateSuccess'));
				}
			})
			.finally(() => {
				loading = false;
			});
	}
</script>

<Modal
	bind:show
	title={I18n.t('console.settings.metadata.update')}
	footer={{
		confirm: {
			text: I18n.t('console.common.updateField', {
				field: I18n.t('console.settings.metadata.metadata')
			})
		},
		cancel: {
			text: I18n.t('console.common.cancel')
		}
	}}
	on:confirm={handleConfirm}
	{loading}
>
	{#if $subscriberMetadataDefinitionStore.length > 0}
		<SplitControl label="Metadata" caption="Custom fields for selected subscribers">
			{#snippet nested()}
				{#each $subscriberMetadataDefinitionStore as definition}
					<SplitControl label={definition.name}>
						<FormControl>
							<TextInput
								block
								bind:value={metadata[definition.key]}
								placeholder={`Enter ${definition.name.toLowerCase()}`}
							/>
						</FormControl>
					</SplitControl>
				{/each}
			{/snippet}
		</SplitControl>
	{:else}
		<div class="no-metadata">
			{I18n.t('console.settings.metadata.notFound')}
		</div>
	{/if}
</Modal>

<style>
	.no-metadata {
		padding: 20px;
		text-align: center;
		color: var(--text-light);
	}
</style>
