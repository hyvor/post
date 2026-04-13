<script lang="ts">
	import { userNewslettersStore } from '../../lib/stores/userNewslettersStore';
	import NewsletterRow from './NewsletterRow.svelte';
</script>

{#if $userNewslettersStore.length}
	<div class="wrap">
		{#each $userNewslettersStore as newsletter}
			<NewsletterRow newsletterList={newsletter} />
		{/each}
	</div>
{/if}

<style>
	.wrap {
		margin-bottom: 35px;
	}
</style>
