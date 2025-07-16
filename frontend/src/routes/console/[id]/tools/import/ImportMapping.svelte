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
    import {subscriberMetadataDefinitionStore} from "../../../lib/stores/newsletterStore";
    import Selector from "../../../@components/content/Selector.svelte";

    interface Props {
        show: boolean;
    }

    let { show = $bindable(true) }: Props = $props();

    const csvColumns = ['email_address', 'segments', 'first_name', 'last_name', 'custom_field_1', 'custom_field_2'];
    let filteredCsvColumns = $derived.by(() =>
        csvColumns.filter(col => col.toLowerCase().includes(search.toLowerCase()))
    );
    type ColumnKey = typeof csvColumns[number];

    const mapKeys = ['email', 'lists', ...$subscriberMetadataDefinitionStore.map(col => col.key)] as const;
    type MapKey = typeof mapKeys[number];

    let updates: Record<MapKey, string | null> = $state(
        Object.fromEntries(mapKeys.map(key => [key, null])) as Record<MapKey, string | null>
    );
    let showSelector = $state(
        Object.fromEntries(mapKeys.map(key => [key, false]))
    );

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
        // TODO
        console.log('Importing with updates:', updates);

        toast.success('Import will begin shortly.');
        show = false;
    }

</script>

<Modal
    bind:show
    title="Import mapping"
    closeOnOutsideClick={false}
    closeOnEscape={false}
    footer={{
        confirm: {
            text: 'Import'
        }
    }}
    on:confirm={handleImport}
    on:cancel={ () => toast.error('Import cancelled.') }
>
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

            <ActionList>
                {#each filteredCsvColumns as csvColumn}
                    <ActionListItem
                        on:click={() => handleSelect('email', csvColumn) }
                    >
                        {csvColumn}
                    </ActionListItem>
                {/each}
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

            <ActionList>
                {#each filteredCsvColumns as csvColumn}
                    <ActionListItem
                        on:click={() => handleSelect('lists', csvColumn) }
                    >
                        {csvColumn}
                    </ActionListItem>
                {/each}
            </ActionList>
        </Selector>
    </SplitControl>

    {#each $subscriberMetadataDefinitionStore as column}
        <SplitControl label={column.name}>
            <Selector
                name={updates[column.key] ? '' : 'Not mapped'}
                value={updates[column.key] ?? undefined}
                width={300}
                bind:show={showSelector[column.key]}
                isSelected={updates[column.key] !== null}
                handleDeselectClick={() => handleSelect(column.key, null)}
            >
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

                <ActionList>
                    {#each filteredCsvColumns as csvColumn}
                        <ActionListItem
                            on:click={() => handleSelect(column.key, csvColumn) }
                        >
                            {csvColumn}
                        </ActionListItem>
                    {/each}
                </ActionList>
            </Selector>
        </SplitControl>
    {/each}
</Modal>
