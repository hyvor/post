<script lang="ts">
	import { updateNewsletter } from '../../../lib/actions/newsletterActions';
	import {
		newsletterEditingStore,
		newsletterStore,
		updateNewsletterStore
	} from '../../../lib/stores/newsletterStore';
	import type { Newsletter } from '../../../types';
	import SaveDiscard from './SaveDiscard.svelte';

	interface Props {
		keys: (keyof Newsletter)[];
		onsave?: () => void;
	}

	let { keys, onsave }: Props = $props();

	let changes = $derived(
		keys.filter(
			(key) =>
				$newsletterEditingStore[key as keyof Newsletter] !==
				$newsletterStore[key as keyof Newsletter]
		)
	);
	let hasChanges = $derived(changes.length > 0);

	async function onSave() {
		const newsletter = await updateNewsletter(
			changes.reduce(
				(acc, key) => ({
					...acc,
					[key]: $newsletterEditingStore[key as keyof Newsletter]
				}),
				{} as any
			)
		);

		updateNewsletterStore(newsletter);

		onsave?.();
	}

	function onDiscard() {
		$newsletterEditingStore = { ...$newsletterStore };
	}
</script>

{#if hasChanges}
	<SaveDiscard onsave={onSave} ondiscard={onDiscard} />
{/if}
