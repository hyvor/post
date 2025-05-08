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


	export let user: User;

	$: isCurrentUserOwner = user.role === 'owner';
	$: isCurrentUserAdmin = user.role === 'admin';

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

				<div class="badge"><RoleTag size="x-small" role={user.role}></RoleTag></div>
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
