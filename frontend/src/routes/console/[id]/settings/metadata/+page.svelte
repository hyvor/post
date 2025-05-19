<script>
	import { Button } from "@hyvor/design/components";
	import TopBar from "../../../@components/content/TopBar.svelte";
	import IconPlus from "@hyvor/icons/IconPlus";

    let adderOpen = $state(false);
</script>

<TopBar>
	<Button on:click={() => (adderOpen = true)}>
		Add Metadata 
        {#snippet end()}
            <IconPlus />
        {/snippet}
	</Button>
</TopBar>