<script lang="ts">
	import { Button, ButtonGroup, ActionList, ActionListItem, Dropdown, Text, TextInput, IconButton } from '@hyvor/design/components';
	import Selector from '../../@components/content/Selector.svelte';
	import type { List, NewsletterSubscriberStatus } from '../../types';
    import IconBoxArrowInDown from '@hyvor/icons/IconBoxArrowInDown';
    import IconPlus from '@hyvor/icons/IconPlus';
	import SingleBox from '../../@components/content/SingleBox.svelte';
	import AddSubscribers from './AddSubscribers.svelte';
	import SubscriberList from './SubscriberList.svelte';
	import { listStore } from '../../lib/stores/projectStore';
	import { onMount } from 'svelte';
	import IconX from '@hyvor/icons/IconX';

    let key = 1; // for re-rendering
    let status: NewsletterSubscriberStatus | null = null;
    $: statusKey = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'All';

    let showStatus = false;
	let showList = false;

    let currentList: List | null = null;

    let searchVal: string = '';
	let search: string = '';

    let addingManually = false;
	let importing = false;


    function selectList(list: List) {
		showList = false;
		currentList = list;
	}

    function selectStatus(s: NewsletterSubscriberStatus | null) {
		showStatus = false;
		status = s;
	}

    const searchActions = {
		onKeydown: (e: KeyboardEvent) => {
			if (e.key === 'Enter') {
				search = searchVal.trim();
			}
		},
		onBlur: () => {
			if (search !== searchVal) {
				search = searchVal.trim();
			}
		},
		onClear: () => {
			searchVal = '';
			search = '';
		}
	};

    onMount(() => {
        const url = new URL(window.location.href);
        const listId = Number(url.searchParams.get('list'));
        if (listId) {
            const list = $listStore.find((l) => l.id === listId);
            if (list) {
                currentList = list;
            }
        }
    });

</script>

<SingleBox>
    <div class="content">
        <div class="left">
            <Selector
                name="Status"
                bind:show={showStatus}
                value={statusKey}
                width={200}
            >
                <ActionList selection="single" selectionAlign="end">
                    <ActionListItem
                        on:click={() => selectStatus(null)}
                        selected={status === null}
                    >
                        All
                    </ActionListItem>
                    <ActionListItem
                        on:click={() => selectStatus('subscribed')}
                        selected={status === 'subscribed'}
                    >
                        Subscribed
                    </ActionListItem>
                    <ActionListItem
                        on:click={() => selectStatus('unsubscribed')}
                        selected={status === 'unsubscribed'}
                    >
                        Unsubscribed
                    </ActionListItem>
                    <ActionListItem
                        on:click={() => selectStatus('pending')}
                        selected={status === 'pending'}
                    >
                        Pending
                    </ActionListItem>
                </ActionList>
            </Selector>
            <Selector
                name="List"
                bind:show={showList}
                value={currentList ? currentList.name : 'Any'}
                width={200}
                isSelected={!!currentList}
                handleDeselectClick={() => (currentList = null)}
            >
                <ActionList>
                    {#each $listStore as list}
                        <ActionListItem
                            on:click={() => selectList(list)}
                            selected={list.id === list?.id}
                        >
                            {list.name}
                        </ActionListItem>
                    {/each}
                </ActionList>
            </Selector>

            <div class="search-wrap">
                <TextInput
                    bind:value={searchVal}
                    placeholder={'Search...'}
                    style="width:250px"
                    on:keydown={searchActions.onKeydown}
                    on:blur={searchActions.onBlur}
                    size="small"
                >
                    <svelte:fragment slot="end">
                        {#if searchVal.trim() !== ''}
                            <IconButton
                                variant="invisible"
                                color="gray"
                                size={16}
                                on:click={searchActions.onClear}
                            >
                                <IconX size={12} />
                            </IconButton>
                        {/if}
                    </svelte:fragment>
                </TextInput>

                {#if search !== searchVal}
                    <span class="press-enter">
                        ⏎
                    </span>
                {/if}
            </div>
        </div>
        <div class="right">
            <ButtonGroup>
                <Button size="small" on:click={() => (importing = true)}>
                    <IconBoxArrowInDown slot="end" /> Import
                </Button>
                <Button size="small" on:click={() => (addingManually = true)}>
                    <IconPlus slot="end" /> Add Manually
                </Button>
            </ButtonGroup>
        </div>
    </div>

    <SubscriberList {status} {key} list_id={currentList?.id || null} search={search === '' ? null : search}/>

    {#if addingManually}
	    <AddSubscribers bind:show={addingManually} add={() => key += 1}/>
    {/if}
</SingleBox>


<style>
	.content {
        display: flex;
		padding: 20px 25px;
	}
	.left {
		flex: 1;
	}
    .search-wrap {
        display: inline;
		.press-enter {
			color: var(--text-light);
			font-size: 14px;
			margin-left: 4px;
		}
		:global(input) {
			font-size: 14px;
		}
	}
</style>
