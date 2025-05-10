<script lang="ts">
	import { updateProject } from '../../../lib/actions/projectActions';
	import {
		projectEditingStore,
		projectStore,
		updateProjectStore
	} from '../../../lib/stores/projectStore';
	import type { Project } from '../../../types';
	import SaveDiscard from './SaveDiscard.svelte';

	interface Props {
		keys: (keyof Project)[];
		onsave?: () => void;
	}

	let { keys, onsave }: Props = $props();

	let changes = $derived(
		keys.filter(
			(key) =>
				$projectEditingStore[key as keyof Project] !== $projectStore[key as keyof Project]
		)
	);
	let hasChanges = $derived(changes.length > 0);

	async function onSave() {
		const project = await updateProject(
			changes.reduce(
				(acc, key) => ({
					...acc,
					[key]: $projectEditingStore[key as keyof Project]
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
