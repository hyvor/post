<script lang="ts">
    import {
        ActionList,
        ActionListItem,
        IconButton,
        Modal,
        SplitControl,
        TextInput,
        toast
    } from '@hyvor/design/components';
    import IconX from '@hyvor/icons/IconX';
    import {importStore, subscriberMetadataDefinitionStore} from "../../../lib/stores/newsletterStore";
    import Selector from "../../../@components/content/Selector.svelte";
    import {subscriberImport} from "../../../lib/actions/importActions";

    interface Props {
        show: boolean;
        importId: number
        fields: string[]
    }

    let { show = $bindable(true), importId, fields }: Props = $props();

    let filteredFields = $derived.by(() =>
        fields.filter(col => col.toLowerCase().includes(search.toLowerCase()))
    );

    const mapKeys = ['email', 'lists', 'subscribed_at', 'subscribe_ip', ...$subscriberMetadataDefinitionStore.map(col => `metadata_${col.key}`)] as const;
    type MapKey = typeof mapKeys[number];

    let updates: Record<MapKey, string | null> = $state(
        Object.fromEntries(mapKeys.map(key => [key, null])) as Record<MapKey, string | null>
    );
    let showSelector = $state(
        Object.fromEntries(mapKeys.map(key => [key, false]))
    );

    let loading: boolean = $state(false);

    let search = $state('');
    const searchActions = {
        onKeydown: (e: KeyboardEvent) => {
            if (e.key === 'Escape') {
                search = '';
            }
        },
        onClear: () => {
            search = '';
        }
    };

    function handleSelect(key: MapKey, value: string | null) {
        updates[key] = value;
        showSelector[key] = false;
    }

    function handleImport() {
        loading = true;
        subscriberImport(importId, updates)
            .then((data) => {
                toast.success('Import will begin shortly.');
                importStore.update(imports => {
                    const index = imports.findIndex(i => i.id === data.id);
                    if (index !== -1) {
                        imports[index] = { ...imports[index], ...data };
                    } else {
                        imports = [data, ...imports];
                    }
                    return imports;
                });
            })
            .catch(() => {
                toast.error('Error while initializing the import.');
            })
            .finally(() => {
                loading = false;
                show = false;
            });
    }

</script>

<Modal
    bind:show
    title="Import mapping"
    footer={{
        confirm: {
            text: 'Import',
            props: {
                disabled: loading || updates.email === null
            }
        }
    }}
    loading={loading}
    on:confirm={handleImport}
>
    <div class="modal-wrap">
        Select the columns from your CSV file that you want to map to the fields in our system. <strong>A mapping for the Email field is required</strong>. You may leave other fields unmapped if you don't want to import them.

        <SplitControl label="Email">
            <Selector
                name={updates.email ? '' : 'Not mapped'}
                value={updates.email ?? undefined}
                width={300}
                bind:show={showSelector.email}
                isSelected={updates.email !== null}
                handleDeselectClick={ () => handleSelect('email', null) }
            >
                <div class="search-bar">
                    <TextInput
                        bind:value={search}
                        type="text"
                        placeholder="Email"
                        autoFocus={true}
                        on:keydown={searchActions.onKeydown}
                        block
                    >
                        {#snippet end()}
                            {#if search.trim() !== ''}
                                <IconButton
                                    variant="invisible"
                                    color="gray"
                                    size={16}
                                    on:click={searchActions.onClear}
                                >
                                    <IconX size={12} />
                                </IconButton>
                            {/if}
                        {/snippet}
                    </TextInput>
                </div>
                <ActionList>
                    <div class="action-list">
                        {#each filteredFields as filteredColumn}
                            <ActionListItem
                                on:click={() => handleSelect('email', filteredColumn) }
                            >
                                {filteredColumn}
                            </ActionListItem>
                        {/each}
                    </div>
                </ActionList>
            </Selector>
        </SplitControl>

        <SplitControl label="Lists">
            <Selector
                name={updates.lists ? '' : 'Not mapped'}
                value={updates.lists ?? undefined}
                width={300}
                bind:show={showSelector.lists}
                isSelected={updates.lists !== null}
                handleDeselectClick={ () => handleSelect('lists', null) }
            >
                <div class="search-bar">
                    <TextInput
                        bind:value={search}
                        type="text"
                        placeholder="Lists"
                        autoFocus={true}
                        on:keydown={searchActions.onKeydown}
                        block
                    >
                        {#snippet end()}
                            {#if search.trim() !== ''}
                                <IconButton
                                    variant="invisible"
                                    color="gray"
                                    size={16}
                                    on:click={searchActions.onClear}
                                >
                                    <IconX size={12} />
                                </IconButton>
                            {/if}
                        {/snippet}
                    </TextInput>
                </div>
                <ActionList>
                    <div class="action-list">
                        {#each filteredFields as filteredColumn}
                            <ActionListItem
                                on:click={() => handleSelect('lists', filteredColumn) }
                            >
                                {filteredColumn}
                            </ActionListItem>
                        {/each}
                    </div>
                </ActionList>
            </Selector>
        </SplitControl>

        <SplitControl label="Subscribed at">
            <Selector
                name={updates.subscribed_at ? '' : 'Not mapped'}
                value={updates.subscribed_at ?? undefined}
                width={300}
                bind:show={showSelector.subscribed_at}
                isSelected={updates.subscribed_at !== null}
                handleDeselectClick={() => handleSelect('subscribed_at', null)}
            >
                <div class="search-bar">
                    <TextInput
                        bind:value={search}
                        type="text"
                        placeholder="Subscribed at"
                        autoFocus={true}
                        on:keydown={searchActions.onKeydown}
                        block
                    >
                        {#snippet end()}
                            {#if search.trim() !== ''}
                                <IconButton
                                    variant="invisible"
                                    color="gray"
                                    size={16}
                                    on:click={searchActions.onClear}
                                >
                                    <IconX size={12} />
                                </IconButton>
                            {/if}
                        {/snippet}
                    </TextInput>
                </div>
                <ActionList>
                    <div class="action-list">
                        {#each filteredFields as filteredColumn}
                            <ActionListItem
                                on:click={() => handleSelect('subscribed_at', filteredColumn) }
                            >
                                {filteredColumn}
                            </ActionListItem>
                        {/each}
                    </div>
                </ActionList>
            </Selector>
        </SplitControl>

        <SplitControl label="Subscribe IP">
            <Selector
                name={updates.subscribe_ip ? '' : 'Not mapped'}
                value={updates.subscribe_ip ?? undefined}
                width={300}
                bind:show={showSelector.subscribe_ip}
                isSelected={updates.subscribe_ip !== null}
                handleDeselectClick={() => handleSelect('subscribe_ip', null)}
            >
                <div class="search-bar">
                    <TextInput
                        bind:value={search}
                        type="text"
                        placeholder="Subscribe IP"
                        autoFocus={true}
                        on:keydown={searchActions.onKeydown}
                        block
                    >
                        {#snippet end()}
                            {#if search.trim() !== ''}
                                <IconButton
                                    variant="invisible"
                                    color="gray"
                                    size={16}
                                    on:click={searchActions.onClear}
                                >
                                    <IconX size={12} />
                                </IconButton>
                            {/if}
                        {/snippet}
                    </TextInput>
                </div>
                <ActionList>
                    <div class="action-list">
                        {#each filteredFields as filteredColumn}
                            <ActionListItem
                                on:click={() => handleSelect('subscribe_ip', filteredColumn) }
                            >
                                {filteredColumn}
                            </ActionListItem>
                        {/each}
                    </div>
                </ActionList>
            </Selector>
        </SplitControl>

        {#each $subscriberMetadataDefinitionStore as column}
            <SplitControl label={"Metadata: " + column.name}>
                <Selector
                    name={updates[`metadata_${column.key}`] ? '' : 'Not mapped'}
                    value={updates[`metadata_${column.key}`] ?? undefined}
                    width={300}
                    bind:show={showSelector[`metadata_${column.key}`]}
                    isSelected={updates[`metadata_${column.key}`] !== null}
                    handleDeselectClick={() => handleSelect(`metadata_${column.key}`, null)}
                >
                    <div class="search-bar">
                        <TextInput
                            bind:value={search}
                            type="text"
                            placeholder={column.name}
                            autoFocus={true}
                            on:keydown={searchActions.onKeydown}
                            block
                        >
                            {#snippet end()}
                                {#if search.trim() !== ''}
                                    <IconButton
                                        variant="invisible"
                                        color="gray"
                                        size={16}
                                        on:click={searchActions.onClear}
                                    >
                                        <IconX size={12} />
                                    </IconButton>
                                {/if}
                            {/snippet}
                        </TextInput>
                    </div>
                    <ActionList>
                        <div class="action-list">
                            {#each filteredFields as filteredColumn}
                                <ActionListItem
                                    on:click={() => handleSelect(`metadata_${column.key}`, filteredColumn) }
                                >
                                    {filteredColumn}
                                </ActionListItem>
                            {/each}
                        </div>
                    </ActionList>
                </Selector>
            </SplitControl>
        {/each}
    </div>
</Modal>


<style>
    .modal-wrap {
        max-height: 55vh;
        overflow-y: auto;
    }
    .search-bar {
        margin-bottom: 6px;
    }
    .action-list {
        max-height: 160px;
        overflow-y: auto;
    }
</style>
