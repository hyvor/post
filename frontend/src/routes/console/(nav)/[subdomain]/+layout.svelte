<script lang="ts">
	import Nav from '../../@components/Nav/Nav.svelte';
	import { page } from '$app/state';
	import { loadNewsletter } from '../../lib/newsletterLoader';
	import { Loader, toast } from '@hyvor/design/components';
	import NewsletterSelector from '../../@components/Nav/NewsletterSelector.svelte';
	import { userNewslettersStore } from '../../lib/stores/userNewslettersStore';
	import { newsletterStore } from '../../lib/stores/newsletterStore';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	let subdomain = $derived(String(page.params.subdomain));
	let isLoading = $state(true);

	function load() {
		const userNewsletter = $userNewslettersStore.find(
			(n) => n.newsletter.subdomain === subdomain
		);

		if (!userNewsletter) {
			toast.error('Newsletter not found');
			location.href = '/console';
			return;
		}

		loadNewsletter(userNewsletter.newsletter.id)
			.then(() => {
				isLoading = false;
			})
			.catch((e) => {
				toast.error('Unable to load newsletter:' + e.message);
			});
	}

	$effect(() => {
		subdomain;
		load();
	});
</script>

{#if isLoading}
	<div class="full-loader">
		<Loader size="large" />
	</div>
{:else}
	{#key $newsletterStore.subdomain}
		<div class="content">
			{@render children?.()}
		</div>
	{/key}
{/if}

<style>
	.content {
		display: flex;
		flex-direction: column;
		flex: 1;
		width: 100%;
		height: 100%;
		min-width: 0;
	}

	.full-loader {
		width: 100%;
		height: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	@media (max-width: 992px) {
		.main-inner {
			display: block;
		}

		.content {
			padding-bottom: 150px;
			height: initial;
			min-height: calc(100vh - 50px);
		}
	}
</style>
