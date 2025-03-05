<script lang="ts">
	import { Header } from '@hyvor/design/marketing';
	import logo from '../../lib/img/logo.png';
	import { Button, DarkToggle } from '@hyvor/design/components';
	import { page } from '$app/stores';
	import { onMount } from 'svelte';

	let loggedIn = $state(false);

	onMount(() => {
		fetch('/api/auth/check', {
			method: 'POST'
		})
			.then<{ is_logged_in: boolean }>((res) => res.json())
			.then((res) => {
				if (res?.is_logged_in) {
					loggedIn = true;
				}
			});
	});
</script>

<Header {logo} subName="Post" darkToggle={false}>
	{#snippet center()}
		<div class="center">
			<Button
				as="a"
				size="small"
				href="/pricing"
				variant={$page.url.pathname === '/pricing' ? 'fill-light' : 'invisible'}
			>
				Pricing
			</Button>
			<Button
				as="a"
				size="small"
				href="/docs"
				variant={$page.url.pathname.startsWith('/docs') ? 'fill-light' : 'invisible'}
			>
				Docs
			</Button>
		</div>
	{/snippet}

	{#snippet end()}
		<div class="end">
			{#if loggedIn}
				<Button as="a" size="small" href="/console">Go to Console &rarr;</Button>
			{:else}
				<Button as="a" size="small" href="/console" variant="invisible">Login</Button>
				<Button as="a" size="small" href="/console?signup">Create a Newsletter</Button>
			{/if}
		</div>
	{/snippet}
</Header>

<style>
	.end {
		display: flex;
		align-items: center;
		gap: 5px;
	}

	/* mobile styles */
	@media (max-width: 768px) {
		.center {
			display: flex;
			flex-direction: column;
		}

		.center {
			display: flex;
			flex-direction: column;
			gap: 5px;
		}
	}

	@media (max-width: 992px) {
		.center {
			display: flex;
			flex-direction: column;
			gap: 5px;
		}

		.end {
			flex-direction: column;
			gap: 5px;
			align-items: center;
		}
	}
</style>
