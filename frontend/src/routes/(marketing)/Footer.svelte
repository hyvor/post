<script lang="ts">
	import { FooterLinkList, Footer } from '@hyvor/design/marketing';
	// import { getMarketingI18n } from './locale';

	// const I18n = getMarketingI18n();
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
		<div>
			<div class="row first">
				<!-- <FooterLinkList title="Hyvor Post">
					<a href="/console">Console</a>
					<a href="/{I18n.getLocale()}/pricing">Pricing</a>
					<a href="/docs">Docs</a>
				</FooterLinkList> -->

				<!-- <FooterLinkList title="Legal">
					<a href="/terms" target="_blank">Terms of Service</a>
					<a href="/privacy" target="_blank">Privacy Policy</a>
					<a href="https://hyvor.com/compliance" target="_blank">Compliance</a>
				</FooterLinkList> -->

				<FooterLinkList title="HYVOR">
					<a href="https://hyvor.com" target="_blank">hyvor.com</a>
					<a href="https://hyvor.com/about" target="_blank">About</a>
					<a href="https://hyvor.com/security" target="_blank">Security</a>
					<a href="https://status.hyvor.com" target="_blank">System Status</a>
					<a href="https://hyvor.com/blog" target="_blank">Blog</a>
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
