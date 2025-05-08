<script lang="ts">
	import {
		TableRow,
		Switch,
		Button,
		IconButton,
		Dropdown,
		ActionList,
		ActionListItem,
		confirm,
		toast
	} from '@hyvor/design/components';
	import type { User } from '../../../types';
	import ProfilePicture from '../../../@components/utils/ProfilePicture.svelte';
	import RoleTag from '../../../@components/Nav/RoleTag.svelte';
	import { projectStore } from '../../../lib/stores/projectStore';
	import IconTrash from '@hyvor/icons/IconTrash';

	export let user: User;

	async function handleRemove() {
		const confirmed = await confirm({
			title: "Remove Admin",
			content: "Are you sure you want to remove this admin?",
			confirmText: "Yes",
			cancelText: "Cancel",
			danger: true
		});
		if (confirmed) {
			confirmed.loading("Removing...");
		}
	}
	console.log($projectStore)
</script>

<TableRow>
	<div>
		<div class="profile">
			<ProfilePicture size={25} user={user.user} />
			<div class="name-badge">
				<div class="name">
					{user.user.name}
					{#if user.user.username}
						<span class="username">@{user.user.username}</span>
					{/if}
				</div>

				<div class="badge">
					<RoleTag size="x-small" role={user.role} />
				</div>
				{#if user.role == 'owner' && $projectStore.user_role != 'owner'}
					<IconButton size={27}>
						<IconTrash size={13} />
					</IconButton>
				{/if}
			</div>
		</div>
	</div>

</TableRow>

<style>
	.profile {
		display: flex;
		align-items: center;
	}

	.name-badge {
		margin-left: 6px;
	}

	.username {
		color: var(--text-light);
		font-size: 12px;
	}

	.badge {
		font-weight: 600;
		text-transform: uppercase;
	}
	.badge :global(span) {
		font-size: 8px;
	}
</style>
