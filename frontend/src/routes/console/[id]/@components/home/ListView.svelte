<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { consoleUrl } from '../../../lib/consoleUrl';
    import ListRow from './ListRow.svelte';
	import Stat from './Stat.svelte';

    // TODO: Remove fake data
    const lists = [
        {
            id: 1,
            name: 'Pytorch newsletter',
            subscribers_count: {
                total: 300,
                last_30d: 100
            }
        },
        {
            id: 2,
            name: 'My super newsletter',
            subscribers_count: {
                total: 200,
                last_30d: 50
            }
        },
        {
            id: 3,
            name: 'SaaS newsletter',
            subscribers_count: {
                total: 100,
                last_30d: 20
            }
        }
    ]
</script>

<div id="list-view">
    <div class="list-header">
        Lists
        <Button color="accent">Create List</Button>
    </div>
    {#each lists as list}
        <ListRow {list} />
    {/each}
</div>

<style>
    .list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        padding-left: 50px;
        padding-right: 50px;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        padding-left: 50px;
        padding-right: 50px;
        border-left: 3px solid transparent;
        position: relative;
        border-radius: 20px;
        cursor: pointer;
    }

    .list-item:hover {
		background: var(--hover);
	}

    .list-title {
		width: 300px;
		font-weight: 600;
		word-break: break-all;
	}
</style>