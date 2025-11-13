<script lang="ts">
	import { Header } from '@hyvor/design/marketing';
	import { Button } from '@hyvor/design/components';
	import { page } from '$app/stores';
</script>

<Header logo="/img/logo.png" subName="Post" darkToggle={false} href="/">
	{#snippet center()}
		<div class="center">
			<Button
				as="a"
				size="small"
				href={`/pricing`}
				variant={$page.url.pathname === `/pricing` ? 'fill-light' : 'invisible'}
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
			<Button as="a" size="small" href="/console">Go to Console &rarr;</Button>
		</div>
	{/snippet}
</Header>

<style>
	:global(.container) {
		width: var(--width) !important;
		max-width: var(--max-width) !important;
	}
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
