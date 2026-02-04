<script>
	import { Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
</script>

<div class="header">
	<Button href="/api/messenger" as="a" target="_blank" color="input">
		Open in new tab
		{#snippet end()}
			<IconBoxArrowUpRight />
		{/snippet}
	</Button>
</div>

<iframe src="/api/messenger" title="Messenger" />

<style>
	.wrap {
		height: 100%;
		display: flex;
		flex-direction: column;
	}
	iframe {
		width: 100%;
		height: 100vh;
		border: none;
		flex: 1;
	}

	.header {
		padding: 15px 25px;
		border-bottom: 1px solid var(--border);
	}
</style>
