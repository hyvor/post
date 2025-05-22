<script lang="ts">
	import { Divider, NavLink, Tag, toast } from '@hyvor/design/components';
	import IconChevronExpand from '@hyvor/icons/IconChevronExpand';
	import IconHouse from '@hyvor/icons/IconHouse';
	import IconPeople from '@hyvor/icons/IconPeople';
	import IconSend from '@hyvor/icons/IconSend';
	import IconGear from '@hyvor/icons/IconGear';
	import IconTools from '@hyvor/icons/IconTools';
	import NavItem from './NavItem.svelte';
	import { projectStore } from '../../lib/stores/newsletterStore';
	import { page } from '$app/state';
	import { getI18n } from '../../lib/i18n';
	import { selectingProject } from '../../lib/stores/consoleStore';

	let width: number;

	const I18n = getI18n();

	function triggerProjectSelector() {
		selectingProject.set(true);
	}
</script>

<svelte:window bind:innerWidth={width} />

<div class="wrap hds-box">
	<button class="current" on:click={triggerProjectSelector}>
		<div class="left">
			<div class="name">
				{$projectStore.name}
			</div>
		</div>
		<IconChevronExpand />
	</button>

	<div class="nav-links">
		<NavLink
			href={'/console/' + $projectStore.id.toString()}
			active={page.url.pathname === `/console/${$projectStore.id}`}
		>
			<NavItem>
				<IconHouse slot="icon" />
				<span slot="text">{I18n.t('console.nav.home')}</span>
			</NavItem>
		</NavLink>

		<NavLink
			href={'/console/' + $projectStore.id.toString() + '/subscribers'}
			active={page.url.pathname === `/console/${$projectStore.id}/subscribers`}
		>
			<NavItem>
				<IconPeople slot="icon" />
				<span slot="text">{I18n.t('console.nav.subscribers')}</span>
			</NavItem>
		</NavLink>

		<NavLink
			href={'/console/' + $projectStore.id.toString() + '/issues'}
			active={page.url.pathname.startsWith(`/console/${$projectStore.id}/issues`)}
		>
			<NavItem>
				<IconSend slot="icon" />
				<span slot="text">{I18n.t('console.nav.issues')}</span>
			</NavItem>
		</NavLink>

		<Divider margin={15} />

		<NavLink
			href={'/console/' + $projectStore.id.toString() + '/tools'}
			active={page.url.pathname.startsWith(`/console/${$projectStore.id}/tools`)}
		>
			<NavItem>
				<IconTools slot="icon" />
				<span slot="text">{I18n.t('console.nav.tools')}</span>
			</NavItem>
		</NavLink>

		<NavLink
			href={'/console/' + $projectStore.id.toString() + '/settings'}
			active={page.url.pathname.startsWith(`/console/${$projectStore.id}/settings`)}
		>
			<NavItem>
				<IconGear slot="icon" />
				<span slot="text">{I18n.t('console.nav.settings')}</span>
			</NavItem>
		</NavLink>
	</div>
</div>

<style lang="scss">
	.wrap {
		padding-bottom: 15px;
		padding-top: 5px;
	}
	.current {
		margin: 10px;
		display: flex;
		align-items: center;
		text-align: left;
		width: calc(100% - 20px);
		padding: 10px 20px;
		border-radius: var(--box-radius);
		cursor: pointer;
		.left {
			flex: 1;
		}
		.name {
			font-weight: 600;
		}
		&:hover {
			background-color: var(--hover);
		}
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
		.current {
			margin: 0px auto;
			margin-bottom: 5px;
		}
		.current .left {
			display: flex;
			gap: 10px;
			align-items: center;
		}
	}
</style>
