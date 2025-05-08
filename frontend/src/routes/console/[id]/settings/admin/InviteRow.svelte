<script lang="ts">
	import { Button, Tag } from '@hyvor/design/components';
	import { IconButton } from '@hyvor/design/components';
	import { toast } from '@hyvor/design/components';
	import type { Invite } from '../../../types';
	import ProfilePicture from '../../../@components/utils/ProfilePicture.svelte';
	import RoleTag from '../../../@components/Nav/RoleTag.svelte';
	import IconTrash from '@hyvor/icons/IconTrash';

	export let invite: Invite;

	let lastResentTime = 0;

	function handleResend() {
		if (lastResentTime) {
			const timeSinceLastResend = Date.now() - lastResentTime;
			if (timeSinceLastResend < 1000 * 60) {
				// 1 minute
				return toast.error('Please wait a minute before resending an invite');
			}
		}
		lastResentTime = Date.now();

		resendModInvite(invite.id);

		toast.success('Invitation resent');
	}

	function handleDelete() {
		deleteModInvite(invite.id);
		dispatch('delete');
	}
</script>

<div class="invite">
	<div class="user">
		<ProfilePicture user={invite.user} />

		<div class="name-badge">
			<div class="name">{invite.user.name}</div>
			<div class="badge"><RoleTag size="x-small" role={invite.role}></RoleTag></div>
		</div>

		<div class="buttons">
			<Button style="light" on:click={handleResend} size="small">
                Resend
            </Button>
			<IconButton size={27} on:click={handleDelete}>
                <IconTrash size={13} />
            </IconButton>
		</div>
	</div>
</div>

<style>
	.user {
		display: flex;
		padding: 10px 15px;
		border-radius: 20px;
		align-items: center;
	}
	.user:hover {
		background-color: var(--hover);
	}
	.name-badge {
		flex: 1;
		margin-left: 8px;
		display: flex;
		flex-direction: column;
	}
	.buttons {
		display: flex;
		gap: 10px;
		align-items: center;
	}
	.badge {
		font-weight: 600;
		text-transform: uppercase;
	}
	.badge :global(span) {
		font-size: 8px;
	}
</style>
