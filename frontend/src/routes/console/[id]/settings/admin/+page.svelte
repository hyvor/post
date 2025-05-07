<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import TopBar from '../../../@components/content/TopBar.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import AdminInvitationModal from './AdminInvitationModal.svelte';


	let inviterOpen = false;
	
</script>

<TopBar>
	<Button on:click={() => (inviterOpen = true)}>
		Add Moderator 
        {#snippet end()}
            <IconPlus />
        {/snippet}
	</Button>

	{#if inviterOpen}
		<AdminInvitationModal
            bind:show={inviterOpen}
            on:close={() => (inviterOpen = false)}
        />
	{/if}
</TopBar>


