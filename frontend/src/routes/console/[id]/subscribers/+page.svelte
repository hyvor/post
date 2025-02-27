<script lang="ts">
	import { Button, ButtonGroup, ActionList, ActionListItem } from '@hyvor/design/components';
	import Selector from '../@components/content/Selector.svelte';
	import type { NewsletterSubscriberStatus } from '../../types';
    import IconBoxArrowInDown from '@hyvor/icons/IconBoxArrowInDown';
    import IconPlus from '@hyvor/icons/IconPlus';
	import SingleBox from '../@components/content/SingleBox.svelte';
	import AddSubscribers from './AddSubscribers.svelte';


    let status: NewsletterSubscriberStatus = 'subscribed';
    let showStatus = false;
	let showSegment = false;

    let addingManually = false;
	let importing = false;

    $: statusKey = 'subscribed';

    function selectStatus(s: NewsletterSubscriberStatus) {
		showStatus = false;
		status = s;
	}

</script>
<SingleBox>
    <div class="content">
        <div class="left">
            <Selector name="Status" bind:show={showStatus} value={statusKey} width={200}>
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

    {#if addingManually}
	    <AddSubscribers bind:show={addingManually} />
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
