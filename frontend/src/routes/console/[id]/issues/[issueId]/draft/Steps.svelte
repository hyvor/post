<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import Step from './Step.svelte';
	import IconArrowRightShort from '@hyvor/icons/IconArrowRightShort';
	import IconArrowLeftShort from '@hyvor/icons/IconArrowLeftShort';
	import { draftStepStore } from './draftStore';
	import { goto } from '$app/navigation';
	import { consoleUrlWithNewsletter } from '../../../../lib/consoleUrl';
	import IconSend from '@hyvor/icons/IconSend';

	const sections = ['content', 'audience'] as const;

	function handleBack() {
		if ($draftStepStore === 'content') {
			goto(consoleUrlWithNewsletter('/issues'));
			return;
		}

		const currentIndex = sections.indexOf($draftStepStore);
		const previousSection = sections[currentIndex - 1];

		if (previousSection) {
			draftStepStore.set(previousSection);
		}
	}

	function handleNext() {
		const currentIndex = sections.indexOf($draftStepStore);
		const nextSection = sections[currentIndex + 1] || 'audience';
		draftStepStore.set(nextSection);
	}
</script>

<div class="wrap">
	<div class="left">
		<Button color="input" onclick={handleBack}>
			Back
			{#snippet start()}
				<IconArrowLeftShort />
			{/snippet}
		</Button>
	</div>
	<div class="steps">
		<Step key="content" name="Content" />
		<Step key="audience" name="Audience & Send" />
	</div>
	<div class="right">
		{#if $draftStepStore === 'audience'}
			<Button onclick={handleNext}>
				Send Issue
				{#snippet end()}
					<IconSend size={14} />
				{/snippet}
			</Button>
		{:else}
			<Button onclick={handleNext}>
				Next
				{#snippet end()}
					<IconArrowRightShort />
				{/snippet}
			</Button>
		{/if}
	</div>
</div>

<style>
	.wrap {
		border-top: 1px solid var(--border);
		padding: 15px 30px;
		display: flex;
		align-items: center;
	}
	.steps {
		display: flex;
		gap: 20px;
		flex: 1;
		justify-content: center;
	}

	.left,
	.right {
		width: 150px;
	}
	.right {
		text-align: right;
	}
</style>
