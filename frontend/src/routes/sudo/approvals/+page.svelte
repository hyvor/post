<script lang="ts">
    import { onMount } from "svelte";
    import { getApprovals, approve } from "../lib/actions/approvalActions";
    import type {Approval} from "../types";
	import { IconMessage, LoadButton, Loader, toast, confirm } from "@hyvor/design/components";
    import ApprovalRow from "./ApprovalRow.svelte";
    import {ITEMS_PER_PAGE} from "../lib/generalActions";
    import ApprovalModal from "./ApprovalModal.svelte";

    let loading = $state(true);
    let hasMore = $state(true);
    let loadingMore = $state(false);
    let error: null | string = $state(null);
    let approvals: Approval[] = $state([]);

    let showModal = $state(false);
    let selectingApproval: Approval | undefined = $state(undefined);

    function load(more = false) {
        more ? (loadingMore = true) : (loading = true);

        getApprovals(null, ITEMS_PER_PAGE, more ? approvals.length : 0)
            .then((data) => {
                approvals = more ? [...approvals, ...data] : data;
                hasMore = data.length === ITEMS_PER_PAGE;
            })
            .catch((e) => {
                error = e.message;
            })
            .finally(() => {
                loading = false;
                loadingMore = false;
            });
    }

    function handleApproveOrReject(id: number, action: 'approved' | 'rejected') {
        approve(id, action)
            .then(() => {
                toast.success("Approval status updated");
            })
            .catch((e) => {
                toast.error("Failed to approve: ", e.message);
            })
            .finally(() => {
                showModal = false;
                window.location.reload();
            });
    }

    function handleSelect(approval: Approval) {
        showModal = true;
        selectingApproval = approval;
    }

    async function onApprove(id: number) {
        const confirmation = await confirm({
            title: 'Confirm approval',
            content: 'Are you sure you want to approve this request?'
        })
        if (confirmation) {
            handleApproveOrReject(id, 'approved');
            console.log("Approved: ", id)
        }
    }

    async function onReject(id: number) {
        const confirmation = await confirm({
            title: 'Confirm rejection',
            content: 'Are you sure you want to reject this request?'
        })
        if (confirmation) {
            handleApproveOrReject(id, 'rejected');
            console.log("Rejected: ", id)
        }
    }

    onMount(() => {
        load();
    });

</script>

{#if loading}
    <Loader full />
{:else if error}
    <IconMessage error message={error} />
{:else if approvals.length === 0}
    <IconMessage empty message="No approvals found" />
{:else}
    <div class="list">
        {#each approvals as approval (approval.id)}
            <ApprovalRow {approval} {handleSelect} />
        {/each}
        <LoadButton
            text="Load More"
            loading={loadingMore}
            show={hasMore}
            on:click={() => load(true)}
        />
    </div>

    {#if showModal && selectingApproval}
        <ApprovalModal
            bind:show={showModal}
            approval={selectingApproval}
            {onApprove}
            {onReject}
        />
    {/if}
{/if}

<style>
    .list {
        flex: 1;
        overflow: auto;
        padding: 20px 30px;
    }
</style>
