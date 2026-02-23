<script lang="ts">
	import { SplitControl, Modal } from '@hyvor/design/components';
	import { toast } from '@hyvor/design/components';
	import { addUser } from '../../../../lib/actions/userActions';
	import { getI18n } from '../../../../lib/i18n';
	import { OrganizationMemberSearch } from '@hyvor/design/cloud';
	import type { User } from '../../../../types';

	interface Props {
		show: boolean;
		onadd: (u: User) => void;
	}

	let { show = $bindable(), onadd }: Props = $props();

	let isInviting = $state(false);
	let addingUserId: number | undefined = $state(undefined);

	const I = getI18n();

	async function handleInvite() {
		if (!addingUserId) {
			toast.error('Please choose a member');
			return;
		}

		isInviting = true;

		addUser({
			user_id: addingUserId,
			role: 'admin' // Hardcoded for now
		})
			.then((user) => {
				onadd(user);
				toast.success(I.t('console.settings.users.added'));
				show = false;
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				isInviting = false;
			});
	}
</script>

<Modal
	title={I.t('console.settings.users.add')}
	bind:show
	footer={{
		cancel: {
			text: I.t('console.common.cancel')
		},
		confirm: {
			text: I.t('console.settings.users.add')
		}
	}}
	on:confirm={handleInvite}
	loading={isInviting}
>
	<SplitControl
		label={I.t('console.settings.users.member')}
		caption={I.t('console.settings.users.memberCaption')}
	>
		<OrganizationMemberSearch bind:selectedUserId={addingUserId} />
	</SplitControl>
</Modal>
