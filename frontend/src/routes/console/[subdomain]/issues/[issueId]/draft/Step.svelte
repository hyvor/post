<script lang="ts">
	import { draftStepStore, type StepKey } from './draftStore';

	interface Props {
		key: StepKey;
		name: string;
	}

	let { key, name }: Props = $props();
</script>

<button class="step" class:active={key === $draftStepStore} onclick={() => ($draftStepStore = key)}
	>{name}</button
>

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
