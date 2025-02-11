<script lang="ts">
	import { NavLink } from '@hyvor/design/components';
	import { page } from '$app/stores';
	import IconCoin from '@hyvor/icons/IconCoin';
	import NavItem from './NavItem.svelte';
</script>

<div class="wrap hds-box">
	<div class="nav-links">
		<NavLink href="/console/billing" active={$page.url.pathname === '/console/billing'}>
			<NavItem>
				<IconCoin slot="icon" />
				<span slot="text">Billing</span>
			</NavItem>
		</NavLink>
	</div>
</div>

<style lang="scss">
	.wrap {
		padding: 15px 0;
		overflow: hidden;
	}

	@media (max-width: 992px) {
		.wrap {
			border-radius: 0 !important;
			display: flex;
			flex-direction: row-reverse;
			padding-bottom: 0;
			padding-top: 0;
		}

		.nav-links {
			display: flex;
			flex: 1;
			:global(a .middle),
			:global(a .end) {
				display: none;
			}
			:global(a .start) {
				margin-right: 0 !important;
			}
			:global(a) {
				border-left: none !important;
				border-bottom: 3px solid transparent;
			}
			:global(a.active) {
				border-bottom-color: var(--accent);
			}
		}
	}
</style>
