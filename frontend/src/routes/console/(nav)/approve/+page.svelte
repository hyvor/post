<script lang="ts">
    import SingleBox from "../../@components/content/SingleBox.svelte";
	import { approvalStore } from "../../lib/stores/consoleStore";
    import ApprovalForm from "./ApprovalForm.svelte";
    import Approved from "./Approved.svelte";
</script>

<SingleBox>
    <div class="content">
        {#if $approvalStore.is_approved}
            <Approved />
        {:else}
            <ApprovalForm />
        {/if}
    </div>
</SingleBox>


<style>
    .content {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 20px 30px;
        overflow: auto;
    }
</style>
