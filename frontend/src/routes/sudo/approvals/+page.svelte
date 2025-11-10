<script lang="ts">
    import {onMount} from "svelte";
    import {getApprovals, approve, APPROVAL_STATUS_FILTERS} from "../lib/actions/approvalActions";
    import type {Approval} from "../types";
    import {
        IconMessage,
        LoadButton,
        Loader,
        TextInput,
        IconButton,
        Dropdown,
        Button,
        ActionList,
        ActionListItem,
        toast,
        confirm
    } from "@hyvor/design/components";
    import IconX from "@hyvor/icons/IconX"
    import IconCaretDown from "@hyvor/icons/IconCaretDown";
    import ApprovalRow from "./ApprovalRow.svelte";
    import {ITEMS_PER_PAGE} from "../lib/generalActions";
    import ApprovalModal from "./ApprovalModal.svelte";
    import {approvalStore} from "../lib/stores/sudoStore";

    let loading = $state(true);
    let hasMore = $state(true);
    let loadingMore = $state(false);
    let error: null | string = $state(null);

    let showModal = $state(false);
    let selectingApproval: Approval | undefined = $state(undefined);

    let search: number | undefined = $state(undefined);
    let searchValue: number | undefined = $state(undefined);

    let statusFilter: keyof typeof APPROVAL_STATUS_FILTERS | undefined = $state(undefined);
    let statusDropdownShow = $state(false);

    function load(more = false) {
        more ? (loadingMore = true) : (loading = true);

        getApprovals(
            search ?? null,
            statusFilter ?? null,
            ITEMS_PER_PAGE,
            more ? $approvalStore.length : 0
        )
            .then((data) => {
                if (more) {
                    approvalStore.update(approvals => [...approvals, ...data]);
                } else {
                    approvalStore.set(data);
                }
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

    function handleApproveRejectPending(approval: Approval, action: 'approved' | 'rejected' | 'pending') {
        approve(approval, action)
            .then((data) => {
                toast.success("Approval status updated");
                approvalStore.update(approvals => {
                    const index = approvals.findIndex(a => a.id === approval.id);
                    if (index !== -1) {
                        approvals[index] = {...approvals[index], ...data};
                    }
                    return approvals;
                });
            })
            .catch((e) => {
                toast.error("Failed to approve: ", e.message);
            })
            .finally(() => {
                showModal = false;
            });
    }

    function handleSelect(approval: Approval) {
        showModal = true;
        selectingApproval = approval;
    }

    async function onApprove(approval: Approval) {
        const confirmation = await confirm({
            title: 'Confirm approval',
            content: 'Are you sure you want to approve this request?'
        })
        if (confirmation) {
            handleApproveRejectPending(approval, 'approved');
        }
    }

    async function onReject(approval: Approval) {
        const confirmation = await confirm({
            title: 'Confirm rejection',
            content: 'Are you sure you want to reject this request?'
        })
        if (confirmation) {
            handleApproveRejectPending(approval, 'rejected');
        }
    }

    async function onMarkAsPending(approval: Approval) {
        const confirmation = await confirm({
            title: 'Confirm mark as pending',
            content: 'Are you sure you want to mark this request as pending?'
        })
        if (confirmation) {
            handleApproveRejectPending(approval, 'pending');
        }
    }

    function handleSearchKeyup(e: any) {
        if (e.key === 'Enter') {
            searchValue = search;
            load();
        }

        if (e.key === 'Escape') {
            handleSearchClear();
        }
    }

    function handleSearchClear() {
        search = undefined;
        searchValue = undefined;
        load();
    }

    function onStatusClick(val: any) {
        statusFilter = val;
        load();
        statusDropdownShow = false;
    }

    onMount(() => {
        load();
    });

</script>

{#if loading}
    <Loader full/>
{:else if error}
    <IconMessage error message={error}/>
{:else}
    <div class="top">
        <TextInput type='number' min=1 bind:value={search} on:keyup={handleSearchKeyup} size="small">
            {#snippet start()}
                User ID
            {/snippet}
            {#snippet end()}
                <div style="display: flex; flex-direction:column; align-items: center; width: 25px;">
                    {#if searchValue}
                        <IconButton size={16} on:click={handleSearchClear}>
                            <IconX size={12}/>
                        </IconButton>
                    {/if}

                    {#if search !== searchValue}
                        &nbsp; ⏎
                    {/if}
                </div>
            {/snippet}
        </TextInput>
        <Dropdown bind:show={statusDropdownShow}>
            {#snippet trigger()}
                <Button color="input" size="small">
                    {#snippet start()}
                        Status
                    {/snippet}
                    <div class="dropdown-label">
                        {statusFilter ? APPROVAL_STATUS_FILTERS[statusFilter] : 'None'}
                    </div>
                    {#if statusFilter}
                        <IconButton
                                size={14}
                                style="margin-left:4px;"
                                color="gray"
                                on:click={(e) => {
									e.stopPropagation();
									statusFilter = undefined;
									load();
								}}
                        >
                            <IconX size={10}/>
                        </IconButton>
                    {/if}
                    {#snippet end()}
                        <IconCaretDown size={14}/>
                    {/snippet}
                </Button>
            {/snippet}
            {#snippet content()}
                <ActionList>
                    {#each Object.entries(APPROVAL_STATUS_FILTERS) as [key, value]}
                        <ActionListItem on:select={() => onStatusClick(key)}
                        >{value}</ActionListItem
                        >
                    {/each}
                </ActionList>
            {/snippet}
        </Dropdown>
    </div>

    {#if $approvalStore.length === 0}
        <IconMessage empty message="No approvals found"/>
    {:else}
        <div class="list">
            {#each $approvalStore as approval (approval.id)}
                <ApprovalRow {approval} {handleSelect}/>
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
                    {onMarkAsPending}
            />
        {/if}
    {/if}
{/if}

<style>
    .top {
        padding: 20px 0 0 30px;
    }

    .list {
        flex: 1;
        overflow: auto;
        padding: 20px 30px;
    }

    .dropdown-label {
        font-weight: normal;
    }
</style>
