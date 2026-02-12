<script lang="ts">
	import { SplitControl, Modal } from '@hyvor/design/components';
	import { toast } from '@hyvor/design/components';
	import { addUser } from '../../../../lib/actions/userActions';
	import { getI18n } from '../../../../lib/i18n';
	import { OrganizationMemberSearch } from '@hyvor/design/cloud';
	import type { User } from '../../../../types';

	export let show: boolean;
	export let refreshUsers: (u: User) => void;

	let isInviting = false;
	let invitingUserId: number | undefined = undefined;

	const I = getI18n();

	async function handleInvite() {
		if (!invitingUserId) {
			return;
		}

		isInviting = true;

		addUser({
			user_id: invitingUserId,
			role: 'admin' // Hardcoded for now
		})
			.then((user) => {
				refreshUsers(user);
				toast.success(I.t('console.settings.users.inviteSent'));
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				isInviting = false;
				show = false;
			});
	}
</script>

<Modal
	title={I.t('console.settings.users.inviteNewAdmin')}
	bind:show
	footer={{
		cancel: {
			text: I.t('console.common.cancel')
		},
		confirm: {
			text: I.t('console.settings.users.invite')
		}
	}}
	on:confirm={handleInvite}
>
	<SplitControl
		label={I.t('console.settings.users.username')}
		caption={I.t('console.settings.users.usernameCaption')}
	>
		<OrganizationMemberSearch bind:selectedUserId={invitingUserId} />
	</SplitControl>
</Modal>

<style>
</style>
