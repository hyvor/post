<script lang="ts">
    import {
        ActionList,
        ActionListItem,
        Button,
        Dropdown,
        IconButton,
        IconMessage,
        LoadButton,
        Loader,
        TextInput,
        toast
    } from "@hyvor/design/components";
    import {onMount} from "svelte";
    import {subscriberImportStore} from "../lib/stores/sudoStore";
    import SubscriberImportRow from "./SubscriberImportRow.svelte";
    import type {SubscriberImport} from "../types";
    import IconX from "@hyvor/icons/IconX";
    import IconCaretDown from "@hyvor/icons/IconCaretDown";
    import {ITEMS_PER_PAGE} from "../lib/generalActions";
    import {approveSubscriptionImport, getSubscriberImports} from "../lib/actions/subscriberImportActions";
    import SubscriberImportModal from "./SubscriberImportModal.svelte";
    import {IMPORT_STATUS_FILTERS} from "../lib/actions/approvalActions";

    let loading = $state(true);
    let hasMore = $state(true);
    let loadingMore = $state(false);
    let error: string | null = $state(null);

    let showModal = $state(false);
    let selectedSubscriberImport: SubscriberImport | undefined = $state(undefined);

    let search: string | undefined = $state(undefined);
    let searchValue: string | undefined = $state(undefined);

    let statusFilter: keyof typeof IMPORT_STATUS_FILTERS | undefined = $state('pending_approval');
    let statusDropdownShow = $state(false);

    function load(more = false) {
        more ? (loadingMore = true) : (loading = true);

        getSubscriberImports(
            search ?? null,
            statusFilter ?? null,
            ITEMS_PER_PAGE,
            more ? $subscriberImportStore.length : 0
        )
            .then((data) => {
                if (more) {
                    subscriberImportStore.update(subscriberImports => [...subscriberImports, ...data]);
                } else {
                    subscriberImportStore.set(data);
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

    function handleSelect(subscriberImport: SubscriberImport) {
        selectedSubscriberImport = subscriberImport;
        showModal = true;
    }

    function onApprove(subscriberImport: SubscriberImport) {
        approveSubscriptionImport(subscriberImport.id)
            .then((res) => {
                toast.success("Subscriber import approved successfully.");
                subscriberImportStore.update((subscriberImports) =>
                    subscriberImports.filter((si) => (si.id !== res.id))
                );
                showModal = false;
            })
            .catch((e) => {
                toast.error(e.message);
            });
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
    })
</script>

{#if loading}
    <Loader full/>
{:else if error}
    <IconMessage error message={error}/>
{:else}
    <div class="top">
        <TextInput bind:value={search} on:keyup={handleSearchKeyup} size="small">
            {#snippet start()}
                Subdomain
            {/snippet}
            {#snippet end()}
                <div class="search-icons">
                    <span class="enter">
                    {#if search !== searchValue}
                        &nbsp; ⏎
                    {/if}
                    </span>
                    <span class="clear">
                    {#if searchValue}
                            <IconButton size={16} on:click={handleSearchClear}>
                                <IconX size={12}/>
                            </IconButton>
                    {/if}
                    </span>
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
                        {statusFilter ? IMPORT_STATUS_FILTERS[statusFilter] : 'None'}
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
                    {#each Object.entries(IMPORT_STATUS_FILTERS) as [key, value]}
                        <ActionListItem on:select={() => onStatusClick(key)}
                        >{value}</ActionListItem
                        >
                    {/each}
                </ActionList>
            {/snippet}
        </Dropdown>
    </div>

    {#if $subscriberImportStore.length === 0}
        <IconMessage empty message="No subscriber imports found"/>
    {:else}
        <div class="list">
            {#each $subscriberImportStore as subscriberImport (subscriberImport.id)}
                <SubscriberImportRow {subscriberImport} {handleSelect}/>
            {/each}
            <LoadButton
                    text="Load More"
                    loading={loadingMore}
                    show={hasMore}
                    on:click={() => load(true)}
            />
        </div>

        {#if showModal && selectedSubscriberImport}
            <SubscriberImportModal
                    bind:show={showModal}
                    subscriberImport={selectedSubscriberImport}
                    {onApprove}
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

    .search-icons {
        display: flex;
        gap: 5px;
        align-items: center;

        .enter, .clear {
            width: 20px;
        }

        .enter {
            margin-top: 3px;
        }
    }
</style>
