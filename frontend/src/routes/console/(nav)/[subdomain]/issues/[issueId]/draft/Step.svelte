<script lang="ts">
	import { draftIssueEditingStore, draftStepStore, type StepKey } from './draftStore';

	interface Props {
		key: StepKey;
		name: string;
	}

	let { key, name }: Props = $props();
</script>

<button
	class="step"
	class:active={key === $draftStepStore[$draftIssueEditingStore.id]}
	onclick={() => ($draftStepStore[$draftIssueEditingStore.id] = key)}
>
	{name}
</button>

<style>
	.step {
		font-size: 14px;
		color: var(--text-light);
		cursor: pointer;
		font-weight: 600;
	}

	.step.active {
		color: var(--accent);
	}
</style>
