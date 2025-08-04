<script lang="ts">
    import {slide} from "svelte/transition";
    import type {List} from "$lib/types";
    import Switch from "./Switch.svelte";
    import Button from "../@components/Button.svelte";
    import {resubscribe} from "$lib/actions/subscriptionActions";

    interface Props {
        lists: List[]
        token: string | undefined
        error: string | undefined
    }

    let {lists, token, error = $bindable()}: Props = $props();

    let selectedListsIds: number[] = $state(lists.map((list) => list.id));

    function handleListSwitch(listId: number) {
        return (event: Event) => {
            const checkbox = event.target as HTMLInputElement;
            if (checkbox.checked) {
                selectedListsIds.push(listId);
            } else {
                selectedListsIds = selectedListsIds.filter((id) => id !== listId);
            }
        };
    }

    function handleSelectAll() {
        selectedListsIds = lists.map((list) => list.id);
    }

    function handleDeselectAll() {
        selectedListsIds = [];
    }

    function handleSave() {
        resubscribe(selectedListsIds, token)
            .catch((e) => {
                error = e.message || 'An unexpected error occurred';
            });
    }
</script>

<div class="resubscribe">
    <div
        class="lists"
        transition:slide={{duration: 400}}
        class:hidden={lists.length === 0}
    >
        {#each lists as list (list.id)}
            <label class="list">
                <div class="list-name-description">
                    <div class="list-name">{list.name}</div>
                    <div class="list-description">{list.description}</div>
                </div>
                <Switch checked={selectedListsIds.includes(list.id)} onchange={handleListSwitch(list.id)}/>
            </label>
        {/each}
    </div>

    <div class="select">
        <Button
            color="var(--hp-accent-text-light)"
            backgroundColor="transparent"
            size="x-small"
            style="font-weight: 500;"
            onclick={handleSelectAll}
        >
            Select all
        </Button>
        <Button
            color="var(--hp-accent-text-light)"
            backgroundColor="transparent"
            size="x-small"
            style="font-weight: 500;"
            onclick={handleDeselectAll}
        >
            Deselect all
        </Button>
    </div>

    <Button
        size="small"
        onclick={handleSave}
    >
        Save preferences
    </Button>
</div>


<style>
    .lists {
        margin: auto;
        width: 70%;
    }

    .list {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8em;
        cursor: pointer;
    }

    .list-name-description {
        text-align: left;
    }

    .list-name {
        font-size: 16px;
        font-weight: 600;
    }

    .list-description {
        font-size: 14px;
        color: var(--hp-text-light);
    }

    .select {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin: 5px 9% 10px auto;
        width: 20%;
    }
</style>
