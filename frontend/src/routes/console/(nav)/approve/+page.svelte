<script lang="ts">
    import SingleBox from "../../@components/content/SingleBox.svelte";
    import { approvalStore, userApprovalStatusStore } from "../../lib/stores/consoleStore";
    import ApprovalForm from "./ApprovalForm.svelte";
    import Approved from "./Approved.svelte";
    import { onMount } from "svelte";
    import { getApproval } from "../../lib/actions/approvalActions";
    import { toast, Loader } from "@hyvor/design/components";

    let loading = $state(true);

    onMount(() => {
        if ($userApprovalStatusStore !== 'pending') {
            getApproval()
                .then((res) => {
                    approvalStore.set(res);
                })
                .catch(() => {
                    toast.error('Failed to load approval data');
                })
                .finally(() => {
                    loading = false;
                });
        }
    });
</script>

<SingleBox>
    <div class="content">
        {#if loading}
            <Loader full />
        {:else if $userApprovalStatusStore === 'approved'}
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
