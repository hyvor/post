<script lang="ts">
	import { NavLink, Tag, Tooltip } from '@hyvor/design/components';
	import { page } from '$app/stores';
	import IconCoin from '@hyvor/icons/IconCoin';
	import IconDatabase from '@hyvor/icons/IconDatabase';
	import IconClipboardCheck from '@hyvor/icons/IconClipboardCheck';
	import IconCheckCircle from '@hyvor/icons/IconCheckCircle';
    import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';
	import NavItem from './NavItem.svelte';
	import { getI18n } from '../../lib/i18n';
    import { userApprovalStatusStore } from "../../lib/stores/consoleStore";
    import ApprovalStatusTag from "./ApprovalStatusTag.svelte";

	const I18n = getI18n();
</script>

<div class="wrap hds-box">
	<div class="nav-links">
        <NavLink href="/console/approve" active="{$page.url.pathname === '/console/approve'}">
            <NavItem>
                <IconClipboardCheck slot="icon" />
                <span slot="text">{I18n.t('console.nav.approve')}</span>
            </NavItem>
            {#snippet end()}
                <ApprovalStatusTag status={$userApprovalStatusStore} />
            {/snippet}
        </NavLink>
		<NavLink href="/console/domains" active={$page.url.pathname === '/console/domains'}>
			<NavItem>
				<IconDatabase slot="icon" />
				<span slot="text">{I18n.t('console.nav.domains')}</span>
			</NavItem>
		</NavLink>
        <Tooltip
            text={I18n.t('console.nav.billingTooltip')}
            position="right"
            disabled={$userApprovalStatusStore === 'approved'}
        >
            <NavLink href="/console/billing" active={$page.url.pathname === '/console/billing'} disabled={$userApprovalStatusStore !== 'approved'}>
                <NavItem>
                    <IconCoin slot="icon" />
                    <span slot="text">{I18n.t('console.nav.billing')}</span>
                </NavItem>
            </NavLink>
        </Tooltip>
    </div>
</div>

<style lang="scss">
	.wrap {
		padding: 15px 0;
		overflow: hidden;
	}

	.nav-links :global(a.active) {
		background-color: var(--accent-light-mid);
	}

	@media (max-width: 992px) {
		.wrap {
			width: 100%;
			z-index: 100;
			border-radius: 0 !important;
			padding-top: 5px;
			padding-bottom: 0;
		}
		.nav-links {
			display: flex;
			border-top: 1px solid var(--border);
			overflow-x: auto;

			:global(a .middle) {
				display: none;
			}
			:global(a .start) {
				margin-right: 0 !important;
			}
			:global(a) {
				border-left: none !important;
				border-top: 3px solid transparent;
				flex: 1;
				justify-content: center;
			}
			:global(a.active) {
				border-top-color: var(--accent);
			}
			:global(.line) {
				display: none !important;
			}
		}
	}
</style>
