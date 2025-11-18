<script lang="ts">
	import { onMount } from 'svelte';
	import { confirm } from '$lib/actions/subscriptionActions';
	import Loader from '../@components/Loader.svelte';
	import Notice from '../@components/Notice.svelte';
	import IconEnvelopeCheck from '@hyvor/icons/IconEnvelopeCheck';
	import IconExclamationOctagon from '@hyvor/icons/IconExclamationOctagon';
	import { newsletterStore } from '$lib/archiveStore';

	let isLoading = $state(true);
	let error = $state<string | null>(null);

	onMount(async () => {
		const url = new URL(window.location.href);
		const token = url.searchParams.get('token');

		if (!token) {
			error = 'Invalid confirmation link';
			isLoading = false;
			return;
		}

		confirm(token)
			.catch((e) => {
				error = e.message || 'An unexpected error occurred';
			})
			.finally(() => {
				isLoading = false;
			});
	});
</script>

<div class="container">
	<div class="inner-container hds-box">
		{#if isLoading}
			<div class="loader-wrap">
				<Loader color="var(--hp-box-text)" block>Confirming your subscription...</Loader>
			</div>
		{:else if error}
			<Notice heading="An error occurred" message={error} icon={IconExclamationOctagon} />
		{:else}
			<Notice
				heading="Subscription Confirmed!"
				message="Thank you for confirming your subscription to <strong>{$newsletterStore.name}</strong>!
                    You will start receiving the latest updates straight to your inbox.
                    Stay tuned!"
				icon={IconEnvelopeCheck}
				button
				buttonText="Show Archives"
				buttonLink="/"
			/>
		{/if}
	</div>
</div>

<style>
	.container {
		display: flex;
		flex-direction: column;
		justify-content: center;
		width: 650px;
		margin: auto;
		max-width: 100%;
		text-align: center;
		height: 100vh;
	}

	.inner-container {
		min-height: 409px;
		color: var(--hp-box-text);
		background-color: var(--hp-box);
		box-shadow: var(--hp-box-shadow);
		border: var(--hp-box-border);
		border-radius: var(--hp-box-radius);
	}

	.loader-wrap {
		height: 350px;
	}
</style>
