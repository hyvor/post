<script lang="ts">
	import {
		SplitControl,
		Loader,
		TextInput,
		Callout,
		Button,
		Modal,
		InputGroup,
		Radio

	} from '@hyvor/design/components';
	import { toast } from '@hyvor/design/components';
	import { inviteUser } from '../../../lib/actions/userActions';
	import type { Invite } from '../../../types';
	import {getI18n} from "../../../lib/i18n";
	export let show: boolean;
	export let refreshInvite: (i: Invite) => void

	let usernameOrEmail = '';
	let isInviting = false;

    const I = getI18n();

	async function handleInvite() {
		if (!usernameOrEmail.trim()) {
			return toast.error(I.t('console.settings.users.usernameEmailRequired'));
		}

		const isEmail = usernameOrEmail.includes('@');
		const inviteData = {
			username: isEmail ? undefined : usernameOrEmail,
			email: isEmail ? usernameOrEmail : undefined,
			role : 'admin', // Hardcoded for now
		};

		try {
			isInviting = true;
			const invite = await inviteUser(inviteData);
			refreshInvite(invite);
			toast.success(I.t('console.settings.users.inviteSent'));

		} catch (e: any) {
			toast.error(e.message);
		} finally {
			isInviting = false;
			show = false;
		}
	}

</script>

<Modal
    title={I.t('console.settings.users.inviteNewAdmin')}
    bind:show
    footer={{
        cancel: {
            text: I.t('console.common.cancel'),
        },
        confirm: {
            text: I.t('console.settings.users.invite'),
        }
    }}
	on:confirm={handleInvite}
>
	<Callout type="info">
		<div slot="title">{I.t('console.settings.users.hyvorAccRequired')}</div>
        {I.t('console.settings.users.askAdmin1')}
		<a href="https://hyvor.com/signup" class="link" target="_blank" rel="noreferrer"
			>
            {I.t('console.settings.users.askAdmin2')}
        </a
		>
        {I.t('console.settings.users.askAdmin3')}
	</Callout>

	<SplitControl
		label={I.t('console.settings.users.username')}
		caption={I.t('console.settings.users.usernameCaption')}
	>
		<TextInput bind:value={usernameOrEmail} block />
	</SplitControl>


</Modal>

<style>
</style>
