<script lang="ts">
	import { Loader } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { initNewsletter } from './newslettePageActions';
	import { page } from '$app/state';
	import { issuesStore, newsletterStore } from './newsletterPageStore';
	import NewsletterPage from './NewsletterPage.svelte';

	let loading = $state(true);
	let error = $state('');

	onMount(() => {
		initNewsletter(page.params.slug)
			.then((res) => {
				newsletterStore.set(res.newsletter);
				issuesStore.set(res.issues);
			})
			.catch((err) => {
				error = err.message;
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

{#if loading}
	<div class="loader-wrap">
		<Loader full size="large" />
	</div>
{:else if error}
	{error}
{:else}
	<NewsletterPage />
{/if}

<style>
	.loader-wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100vh;
	}
</style>
