<script>
	import { Button } from '@hyvor/design/components';
	import SettingsBody from '../@components/SettingsBody.svelte';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
</script>

<SettingsTop>
	<Button>
		Add Sending Profile
		{#snippet end()}
			<IconPlus />
		{/snippet}
	</Button>
</SettingsTop>

<SettingsBody>
	<div class="help">
		Sending profiles can be used to configure multiple emails and branding to use when sending
		issues.
	</div>
</SettingsBody>

<style>
	.help {
		font-size: 14px;
		color: var(--text-light);
	}
</style>
