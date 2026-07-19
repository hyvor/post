<script>
	import { DarkToggle, LanguageToggle } from '@hyvor/design/components';
	import AccountNav from './AccountNav.svelte';
	import NewsletterNav from './NewsletterNav.svelte';
	import { isEmbedded } from '../../lib/embedded';
</script>

<div id="nav">
	<div id="nav-wrap">
		{#if !$isEmbedded}
			<div class="account-nav">
				<AccountNav />
			</div>
		{/if}
		<div class="newsletter-nav">
			<NewsletterNav />
		</div>
		{#if !$isEmbedded}
			<div class="bottom">
				<div class="lang hds-box">
					<LanguageToggle position="top" />
					<DarkToggle />
				</div>
			</div>
		{/if}
	</div>
</div>

<style lang="scss">
	.account-nav {
		margin-bottom: 15px;
	}

	#nav {
		width: 280px;
		padding: 15px;
		padding-right: 0;
		flex-shrink: 0;
	}

	#nav-wrap {
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.bottom {
		flex: 1;
		display: flex;
		flex-direction: column;
		justify-content: flex-end;
	}

	.bottom .lang {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 10px 15px;
	}

	@media (max-width: 992px) {
		.account-nav {
			margin-bottom: 0;
		}
		#nav-wrap {
			position: fixed;
			bottom: 0;
			left: 0;
			width: 100%;
			z-index: 100;
			height: initial;
			flex: 1;
		}
		#nav {
			height: 0;
			padding: 0;
		}
	}
</style>
