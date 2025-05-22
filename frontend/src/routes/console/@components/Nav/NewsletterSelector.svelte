<script lang="ts">
	import { Button, Modal } from '@hyvor/design/components';
	import { selectingNewsletter } from '../../lib/stores/consoleStore';
	import IconPlus from '@hyvor/icons/IconPlus';
	import NewsletterList from './NewsletterList.svelte';
</script>

{#if $selectingNewsletter}
	<Modal size="large" bind:show={$selectingNewsletter}>
		{#snippet title()}
			<span class="title">Choose a newsletter </span>
			<Button as="a" href="/console/new" on:click={() => selectingNewsletter.set(false)}>
				Create new newsletter
				{#snippet end()}
					<IconPlus size={12} />
				{/snippet}
			</Button>
		{/snippet}

		<div class="wrap">
			<NewsletterList own={true} />
			<NewsletterList own={false} />
		</div>
	</Modal>
{/if}

<style>
	.wrap {
		padding: 0 15px;
	}

	.title {
		font-size: 20px;
		font-weight: 600;
		margin-right: 10px;
	}
</style>
