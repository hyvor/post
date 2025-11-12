<script lang="ts">
	import { Button, ButtonGroup } from '@hyvor/design/components';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import EditTemplateModal from './EditTemplateModal.svelte';
	import ConfigComponent from './ConfigComponent.svelte';
	import IconCodeSlash from '@hyvor/icons/IconCodeSlash';
	import IconEye from '@hyvor/icons/IconEye';
	import BasicPreviewModal from './modal/BasicPreviewModal.svelte';

	let showEditTemplateModal = false;
	let showPreviewModal = false;
</script>

<SettingsTop>
	<ButtonGroup>
		<Button on:click={() => (showPreviewModal = true)}>
			Preview Email
			{#snippet end()}
				<IconEye size={12} />
			{/snippet}
		</Button>
		<Button on:click={() => (showEditTemplateModal = true)}>
			Edit Template
			{#snippet end()}
				<IconCodeSlash />
			{/snippet}
		</Button>
	</ButtonGroup>
</SettingsTop>

<div class="wrap">
	<ConfigComponent />
</div>

<BasicPreviewModal bind:show={showPreviewModal} />
<EditTemplateModal bind:show={showEditTemplateModal} />

<style>
	.wrap {
		padding: 25px 30px 60px;
		overflow: auto;
		flex: 1;
	}
</style>
