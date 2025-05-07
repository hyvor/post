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
	
	export let show: boolean;

	let loading = false;
	let usernameOrEmail = '';
	let role: 'admin';
	let isInviting = false;

	async function handleInvite() {
		if (!usernameOrEmail.trim()) {
			return toast.error('Username or email is required');
		}

		const isEmail = usernameOrEmail.includes('@');
		const inviteData = {
			username: isEmail ? undefined : usernameOrEmail,
			email: isEmail ? usernameOrEmail : undefined,
			role : role,
		};

		try {
			isInviting = true;
		
		} catch (e: any) {
			toast.error(e.message);
		} finally {
			isInviting = false;
			show = false;
		}
	}

</script>

<Modal 
    title="Invite New Admin"
    bind:show
    footer={{
        cancel: {
            text: 'Cancel',
        }, 
        confirm: {
            text: 'Invite',
        }
    }}
>
	<Callout type="info">
		<div slot="title">HYVOR account required</div>
		Ask your admin to
		<a href="https://hyvor.com/signup" class="link" target="_blank" rel="noreferrer"
			>create a HYVOR account</a
		>
		before inviting.
	</Callout>

	<SplitControl
		label="Username or Email"
		caption="Username or email of the admin's HYVOR account"
	>
		<TextInput bind:value={usernameOrEmail} block />
	</SplitControl>


</Modal>

<style>
</style>
