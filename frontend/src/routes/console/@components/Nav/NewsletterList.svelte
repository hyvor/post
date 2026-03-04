<script lang="ts">
	import { userNewslettersStore } from '../../lib/stores/userNewslettersStore';
	import NewsletterRow from './NewsletterRow.svelte';
</script>

{#if $userNewslettersStore.length}
	<div class="wrap">
		<div class="title-wrap">
			<div class="title">Newsletters you can access</div>
			<div class="description">Your organization owns these newsletters.</div>
		</div>
		{#each $userNewslettersStore as newsletter}
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
