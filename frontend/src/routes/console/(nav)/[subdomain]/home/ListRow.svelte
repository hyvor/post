<script lang="ts">
    import {IconButton, toast, confirm} from '@hyvor/design/components';
    import {listStore, newsletterStore} from '../../../lib/stores/newsletterStore';
    import type {List} from '../../../types';
    import IconTrash from '@hyvor/icons/IconTrash';
    import {deleteList} from '../../../lib/actions/listActions';
    import IconPencil from '@hyvor/icons/IconPencil';
    import ListEditionModal from './ListEditionModal.svelte';

    let {list}: { list: List } = $props();
    let modalOpen = $state(false);

    function truncateDescription(description: string | null): string {
        if (!description) return '(No description)';
        if (description.length > 50) {
            return description.slice(0, 50) + '...';
        }
        return description;
    }

    function onEdit(event: Event) {
        event.stopPropagation();
        event.preventDefault();
        modalOpen = true;
    }

    async function onDelete(event: Event) {
        event.stopPropagation();
        event.preventDefault();

        const confirmation = await confirm({
            title: 'Delete List',
            content: 'Are you sure you want to delete this list?',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            danger: true,
            autoClose: false
        });

        if (!confirmation) return;

        confirmation.loading();

        deleteList(list.id)
            .then(() => {
                toast.success('List deleted successfully');
                listStore.update((lists) => {
                    return lists.filter((l) => l.id !== list.id);
                });
            })
            .catch((err) => {
                toast.error(err.message);
            })
            .finally(() => {
                confirmation.close();
            });
    }
</script>

<div class="list-item">
    <a class="list-content" href={`/console/${$newsletterStore.subdomain}/subscribers?list=${list.id}`}>
        <div class="list-title">
            {list.name || '(Untitled)'}
            <div class="list-description">
                {truncateDescription(list.description)}
            </div>
        </div>
        <div class="list-subscribers">
            <div class="count">
                {list.subscribers_count} Subscribers
            </div>
        </div>
    </a>
    <div class="actions">
        <IconButton
                color="input"
                size="small"
                on:click={(event: Event) => {
				modalOpen = true;
				onEdit(event);
			}}
        >
            <IconPencil size={12}/>
        </IconButton>

        <IconButton color="red" variant="fill-light" size="small" on:click={onDelete}>
            <IconTrash size={12}/>
        </IconButton>
    </div>
</div>

<ListEditionModal bind:modalOpen {list}/>

<style>
    .list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .actions {
        margin-left: 10px;
    }

    .list-content {
        flex: 1;
        padding: 10px;
        padding-left: 15px;
        padding-right: 15px;
        border-left: 3px solid transparent;
        position: relative;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none;
        color: inherit;
    }

    .list-content:hover {
        background: var(--hover);
    }

    .list-title {
        width: 300px;
        font-weight: 600;
        word-break: break-all;
    }

    .list-description {
        margin-top: 5px;
        font-weight: 100;
        font-size: 12px;
        color: var(--text-light);
    }

    .count {
        font-weight: 600;
    }
</style>
