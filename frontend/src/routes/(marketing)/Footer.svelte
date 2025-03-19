<script lang="ts">
	import { FooterLinkList, Footer } from '@hyvor/design/marketing';
</script>

<Footer
	email="post.support@hyvor.com"
	social={{
		x: 'https://x.com/hyvor',
		youtube: null,
		discord: null
	}}
>
	{#snippet center()}
		<div >
			<div class="row first">
				<FooterLinkList title="Product">
					<a href="/console">Console</a>
					<a href="/pricing">Pricing</a>
					<a href="/docs">Docs</a>
				</FooterLinkList>

				<FooterLinkList title="Legal">
					<a href="/terms">Terms of Service</a>
					<a href="/privacy">Privacy Policy</a>
					<a href="https://hyvor.com/compliance" target="_blank">Compliance</a>
				</FooterLinkList>

				<FooterLinkList title="HYVOR">
					<a href="https://hyvor.com" target="_blank">hyvor.com</a>
					<a href="https://hyvor.com/about" target="_blank">About</a>
					<a href="https://hyvor.com/security" target="_blank">Security</a>
					<a href="https://status.hyvor.com" target="_blank">System Status</a>
				</FooterLinkList>
			</div>
		</div>
	{/snippet}
</Footer>

<style>
	.row {
		display: flex;
	}
	.row:not(.first) {
		margin-top: 60px;
	}

	@media (max-width: 992px) {
		.row {
			flex-direction: column;
			gap: 35px;
		}
		.row:not(.first) {
			margin-top: 35px;
		}
	}
</style>
