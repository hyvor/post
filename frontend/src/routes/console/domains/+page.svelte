<script lang="ts">
	import { Button } from "@hyvor/design/components";
	import SingleBox from "../@components/content/SingleBox.svelte";
	import IconPlus from "@hyvor/icons/IconPlus";
	import CreateDomainModal from "./CreateDomainModal.svelte";
	import DomainList from "./DomainList.svelte";

	let showCreateModal = false;
	let domainList: DomainList;

	function handleCreate() {
		domainList.refreshDomains();
	}
</script>

<SingleBox>
    <div class="content">
        <div class="header">
            <div></div>
            <Button on:click={() => showCreateModal = true}>
                Create Domain
            </Button>
        </div>
        <DomainList bind:this={domainList} />
    </div>
</SingleBox>

<CreateDomainModal 
	bind:show={showCreateModal}
	onCreate={handleCreate}
/>

<style>
    .content {
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding: 15px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

</style>
