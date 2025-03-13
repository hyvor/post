<script lang="ts">
	import { Button, ButtonGroup, ActionList, ActionListItem, Dropdown, Text } from '@hyvor/design/components';
	import Selector from '../@components/content/Selector.svelte';
	import type { List, NewsletterSubscriberStatus } from '../../types';
    import IconBoxArrowInDown from '@hyvor/icons/IconBoxArrowInDown';
    import IconPlus from '@hyvor/icons/IconPlus';
	import SingleBox from '../@components/content/SingleBox.svelte';
	import AddSubscribers from './AddSubscribers.svelte';
	import SubscriberList from './SubscriberList.svelte';
	import { listStore } from '../../lib/stores/projectStore';


    let key = 1; // for re-rendering
    let status: NewsletterSubscriberStatus = 'subscribed';
    $: statusKey = status.charAt(0).toUpperCase() + status.slice(1);

    let showStatus = false;
	let showList = false;

    let currentList: List | null = null;

    let addingManually = false;
	let importing = false;


    function selectList(list: List) {
		showList = false;
		currentList = list;
	}

    function selectStatus(s: NewsletterSubscriberStatus) {
		showStatus = false;
		status = s;
	}

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

    <SubscriberList {status} {key} />

    {#if addingManually}
	    <AddSubscribers bind:show={addingManually} add={() => key += 1}/>
    {/if}
</SingleBox>


<style>
	.content {
        display: flex;
		padding: 25px 35px;
	}
	.left {
		flex: 1;
	}
    
</style>
