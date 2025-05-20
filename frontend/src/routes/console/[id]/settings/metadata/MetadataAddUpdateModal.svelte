<script lang="ts">
	import {
		FormControl,
		Modal,
		SplitControl,
		TextInput,
		toast,
		Validation
	} from '@hyvor/design/components';
	import type { SubscriberMetadataDefinition } from '../../../types';
	import {
		createSubscriberMetadataDefinition,
		updateSubscriberMetadataDefinition
	} from '../../../lib/actions/subscriberMetadataActions';
	import { subscriberMetadataDefinitionStore } from '../../../lib/stores/projectStore';
	import { getI18n } from '../../../lib/i18n';

	interface Props {
		show?: boolean;
		metadata?: SubscriberMetadataDefinition;
	}

	let { show = $bindable(false), metadata }: Props = $props();

	let key = $state(metadata?.key || '');
	let name = $state(metadata?.name || '');
	let loading = $state(false);
	let keyError = $state('');

	const I18n = getI18n();

	function validateKey(checkEmpty = false): boolean {
		const k = key.trim();

		if (checkEmpty && k.length === 0) {
			keyError = I18n.t('console.common.cannotBeEmpty', {
				field: I18n.t('console.settings.metadata.key')
			});
			return false;
		}

		if (!checkEmpty && k.length === 0) {
			keyError = '';
			return true;
		}

		if (!/^[a-z0-9_]+$/.test(k)) {
			keyError = I18n.t('console.settings.metadata.keyValdation', {
				characters: 'a-z, 0-9, _'
			});
			return false;
		}

		keyError = '';
		return true;
	}

	function onKeyInput() {
		keyError = '';
		validateKey();
	}

	function handleConfirm() {
		if (!validateKey(true)) {
			return;
		}

		loading = true;

		if (metadata) {
			updateSubscriberMetadataDefinition(metadata.id, name)
				.then((res) => {
					toast.success(
						I18n.t('console.common.updated', {
							field: I18n.t('console.settings.metadata.metadata')
						})
					);
					subscriberMetadataDefinitionStore.update((defs) =>
						defs.map((def) => (def.id === res.id ? res : def))
					);
					show = false;
				})
				.catch((error) => {
					toast.error(error.message);
				})
				.finally(() => {
					loading = false;
				});
		} else {
			createSubscriberMetadataDefinition(key, name)
				.then((res) => {
					toast.success(
						I18n.t('console.common.created', {
							field: I18n.t('console.settings.metadata.metadata')
						})
					);
					subscriberMetadataDefinitionStore.update((defs) => [...defs, res]);
					show = false;
				})
				.catch((error) => {
					toast.error(error.message);
				})
				.finally(() => {
					loading = false;
				});
		}
	}

	const title = metadata
		? I18n.t('console.settings.metadata.update')
		: I18n.t('console.settings.metadata.add');
</script>

<Modal
	bind:show
	{title}
	footer={{
		confirm: {
		}
	}}
	on:confirm={handleConfirm}
	{loading}
>
	<SplitControl
		label={I18n.t('console.settings.metadata.key')}
		caption={I18n.t('console.settings.metadata.keyCaption')}
	>
		<FormControl>
			<TextInput
				block
				placeholder="plan"
				bind:value={key}
				disabled={!!metadata}
				on:input={onKeyInput}
				maxlength={255}
			></TextInput>

			{#if keyError}
				<Validation state="error">{keyError}</Validation>
			{/if}
		</FormControl>
	</SplitControl>
	<SplitControl
		label={I18n.t('console.settings.metadata.displayName')}
		caption={I18n.t('console.settings.metadata.displayNameCaption')}
	>
		<TextInput block placeholder="Subscription Plan" bind:value={name} maxlength={255}
		></TextInput>
	</SplitControl>
</Modal>
