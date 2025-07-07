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
	import { newsletterRoleStore, newsletterStore } from '../../../lib/stores/newsletterStore';
	import IconTrash from '@hyvor/icons/IconTrash';
	import { deleteUser } from '../../../lib/actions/userActions';
    import {getI18n} from "../../../lib/i18n";
	export let refreshUserDelete: (u: User) => void;
	export let user: User;
    const I = getI18n();
	async function handleRemove() {
		const confirmation = await confirm({
			title: I.t('console.settings.users.removeAdminTitle'),
			content: I.t('console.settings.users.removeAdminContent'),
			confirmText: I.t('console.common.yes'),
			cancelText: I.t('console.common.cancel'),
			danger: true
		});
		if (!confirmation) return;

		confirmation.loading();

		deleteUser(user.id)
			.then(() => {
				toast.success(I.t('console.settings.users.removeAdminSuccess'));
				refreshUserDelete(user);
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				confirmation.close();
			});
	}
</script>

<TableRow>
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
		</div>
		<div class="buttons">
			{#if user.role != 'owner' && $newsletterRoleStore == 'owner'}
				<IconButton size={27} on:click={handleRemove}>
					<IconTrash size={13} />
				</IconButton>
			{/if}
		</div>
	</div>
</TableRow>

<style>
	.profile {
		display: flex;
		flex: 1;
		align-items: center;
	}

	.name-badge {
		margin-left: 6px;
	}
	.buttons {
		display: flex;
		gap: 10px;
		align-items: center;
		margin-left: auto;
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
