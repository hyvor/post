<script lang="ts">
	import { page } from '$app/state';
	import { NavLink } from '@hyvor/design/components';
	import IconBrush from '@hyvor/icons/IconBrush';
	import { projectStore } from '../../lib/stores/projectStore';
	import IconEnvelopeAt from '@hyvor/icons/IconEnvelopeAt';
	import IconPeople from '@hyvor/icons/IconPeople';
	import IconCardText from '@hyvor/icons/IconCardText';
	import IconEnvelopeCheck from '@hyvor/icons/IconEnvelopeCheck';
	import IconDatabase from '@hyvor/icons/IconDatabase';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	const prefix = `/console/${$projectStore.id}/settings`;
</script>

<div class="settings">
	<div class="nav hds-box">
		<NavLink href={prefix} active={page.url.pathname === prefix}>
			{#snippet start()}
				<IconCardText />
			{/snippet}
			Project
		</NavLink>

		<NavLink href="{prefix}/design" active={page.url.pathname === prefix + '/design'}>
			{#snippet start()}
				<IconBrush />
			{/snippet}
			Email Design
		</NavLink>

		<NavLink href="{prefix}/users" active={page.url.pathname === prefix + '/users'}>
			{#snippet start()}
				<IconPeople />
			{/snippet}
			Users
		</NavLink>

		<NavLink href="{prefix}/form" active={page.url.pathname === prefix + '/form'}>
			{#snippet start()}
				<IconEnvelopeAt />
			{/snippet}
			Signup Form
		</NavLink>

		<NavLink href="{prefix}/sending" active={page.url.pathname === prefix + '/sending'}>
			{#snippet start()}
				<IconEnvelopeCheck />
			{/snippet}
			Sending Email
		</NavLink>

		<NavLink href="{prefix}/metadata" active={page.url.pathname === prefix + '/metadata'}>
			{#snippet start()}
				<IconDatabase />
			{/snippet}
			Subscriber Metadata
		</NavLink>

		<div class="section-div"></div>
	</div>

	<div class="content hds-box">
		{@render children?.()}
	</div>
</div>

<style>
	.settings {
		display: flex;
		height: 100%;
	}
	.nav {
		width: 315px;
		margin-right: 15px;
		display: flex;
		flex-direction: column;
		flex-shrink: 0;
		height: 100%;
		padding: 25px 0;
		overflow: auto;
	}
	.nav :global(a.active) {
		background-color: var(--accent-light-mid);
	}
	.content {
		flex: 1;
		min-width: 0;
		height: 100%;
		display: flex;
		flex-direction: column;
	}
	.section-div {
		height: 25px;
		flex-shrink: 0;
	}

	@media (max-width: 992px) {
		.settings {
			flex-direction: column;
		}
		.nav {
			width: 100%;
			margin-right: 0;
			margin-bottom: 20px;
		}
	}
</style>
