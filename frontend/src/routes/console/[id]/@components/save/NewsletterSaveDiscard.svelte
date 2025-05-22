<script lang="ts">
	import { updateProject } from '../../../lib/actions/newsletterActions';
	import {
		projectEditingStore,
		projectStore,
		updateProjectStore
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
				$projectEditingStore[key as keyof Newsletter] !==
				$projectStore[key as keyof Newsletter]
		)
	);
	let hasChanges = $derived(changes.length > 0);

	async function onSave() {
		const project = await updateProject(
			changes.reduce(
				(acc, key) => ({
					...acc,
					[key]: $projectEditingStore[key as keyof Newsletter]
				}),
				{} as any
			)
		);

		updateProjectStore(project);

		onsave?.();
	}

	function onDiscard() {
		$projectEditingStore = { ...$projectStore };
	}
</script>

{#if hasChanges}
	<SaveDiscard onsave={onSave} ondiscard={onDiscard} />
{/if}
