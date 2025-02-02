<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { consoleUrl } from '../../../lib/consoleUrl';
    import ListRow from './ListRow.svelte';
	import Stat from './Stat.svelte';
	import CreateListButton from './CreateListButton.svelte';

    let showCreateListModal = false;

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
        <CreateListButton />
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
</style>