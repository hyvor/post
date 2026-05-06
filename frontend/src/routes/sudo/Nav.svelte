<script>
	import { Divider, NavLink } from '@hyvor/design/components';
	import IconClipboardCheck from '@hyvor/icons/IconClipboardCheck';
	import IconBoxArrowInDown from '@hyvor/icons/IconBoxArrowInDown';
	import NavItem from '../console/@components/Nav/NavItem.svelte';
	import { page } from '$app/stores';
	import { statsStore } from './lib/stores/sudoStore.js';
	import { Tag } from '@hyvor/design/components';
	import IconSend from '@hyvor/icons/IconSend';
	import IconJournal from '@hyvor/icons/IconJournal';
	import IconEnvelope from '@hyvor/icons/IconEnvelope';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
</script>

<div class="nav">
	<div class="nav-links hds-box">
		<NavLink href="/sudo/approvals" active={$page.url.pathname === '/sudo/approvals'}>
			<NavItem>
				{#snippet icon()}
					<IconClipboardCheck />
				{/snippet}
				{#snippet text()}
					<span>
						Approvals
						{#if $statsStore.reviewing_approvals > 0}
							<Tag size="x-small" color="orange">
								{$statsStore.reviewing_approvals}
							</Tag>
						{/if}
					</span>
				{/snippet}
			</NavItem>
		</NavLink>
		<NavLink href="/sudo/imports" active={$page.url.pathname === '/sudo/imports'}>
			<NavItem>
				{#snippet icon()}
					<IconBoxArrowInDown />
				{/snippet}
				{#snippet text()}
					<span>
						Subscriber Imports
						{#if $statsStore.pending_imports > 0}
							<Tag size="x-small" color="orange">
								{$statsStore.pending_imports}
							</Tag>
						{/if}
					</span>
				{/snippet}
			</NavItem>
		</NavLink>

		<Divider margin={10} />

		<NavLink href="/sudo/newsletters" active={$page.url.pathname.startsWith('/sudo/newsletters')}>
			<NavItem>
				{#snippet icon()}
					<IconJournal />
				{/snippet}
				{#snippet text()}
					<span>Newsletters</span>
				{/snippet}
			</NavItem>
		</NavLink>
		<NavLink href="/sudo/issues" active={$page.url.pathname.startsWith('/sudo/issues')}>
			<NavItem>
				{#snippet icon()}
					<IconEnvelope />
				{/snippet}
				{#snippet text()}
					<span>Issues</span>
				{/snippet}
			</NavItem>
		</NavLink>

		<Divider margin={10} />

		<NavLink href="/api/messenger" target="_blank">
			<NavItem>
				{#snippet icon()}
					<IconSend />
				{/snippet}
				{#snippet text()}
					<span> Messenger </span>
				{/snippet}
				{#snippet end()}
					<IconBoxArrowUpRight size={14} />
				{/snippet}
			</NavItem>
		</NavLink>
	</div>
</div>

<style lang="scss">
	.nav {
		width: 280px;
		padding: 15px;
		padding-right: 0;
		flex-shrink: 0;
	}

	.nav-links {
		height: calc(100vh - 30px);
		padding: 15px 0;
		overflow: hidden;
	}

	.nav-links :global(a.active) {
		background-color: var(--accent-light-mid);
	}
</style>
