<script lang="ts">
	import { userNewslettersStore } from '../../lib/stores/userNewslettersStore';
	import NewsletterRow from './NewsletterRow.svelte';

	export let own = false;

	const newsletters = own
		? $userNewslettersStore.filter((p) => p.role == 'owner')
		: $userNewslettersStore.filter((p) => p.role != 'owner');
</script>

{#if newsletters.length}
	<div class="wrap">
		<div class="title-wrap">
			<div class="title">
				{#if own}
					Newsletters you own
				{:else}
					Newsletters you are admin
				{/if}
			</div>
			<div class="description">
				{#if own}
					You are the owner of these newsletters. Your subscription applies to all of
					them.
				{:else}
					You are admin on these websites. Your subscription does not apply to them.
				{/if}
			</div>
		</div>
		{#each newsletters as newsletter}
			<NewsletterRow newsletterList={newsletter} />
		{/each}
	</div>
{/if}

<style>
	.wrap {
		margin-bottom: 35px;
	}
	.title-wrap {
		margin-bottom: 15px;
	}
	.title {
		font-weight: 600;
	}
	.description {
		color: var(--text-light);
		font-size: 14px;
	}
</style>
