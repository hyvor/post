<script>
	import { Button, ButtonGroup, Modal } from '@hyvor/design/components';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconEye from '@hyvor/icons/IconEye';
	import SettingsBody from '../@components/SettingsBody.svelte';
	import FormSettings from './FormSettings.svelte';
	import PreviewModal from './PreviewModal.svelte';

	let preview = false;
</script>

<SettingsTop>
	<ButtonGroup>
		<Button on:click={() => (preview = !preview)}>
			Open Preview
			{#snippet end()}
				<IconEye size={12} />
			{/snippet}
		</Button>
		<Button color="input" as="a" href="/docs/form" target="_blank">
			Signup Form Docs
			{#snippet end()}
				<IconBoxArrowUpRight size={12} />
			{/snippet}
		</Button>
	</ButtonGroup>
</SettingsTop>

<SettingsBody>
	<FormSettings />
</SettingsBody>

<PreviewModal bind:preview />
