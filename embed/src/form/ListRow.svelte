<script lang="ts">
    import Switch from "./Switch.svelte";
    import type { List } from "./types";

    interface Props {
        list: List;
    }

    let { list }: Props = $props();
</script>

<label class="list">
    <div class="name-description">
        <div class="name">{list.name}</div>
        <div class="description">{list.description}</div>
    </div>
    <div class="checkbox">
        <Switch checked={true} />
    </div>
</label>

<style>
    .list {
        display: flex;
        justify-content: space-between;
        padding: 0.4em 0;
        cursor: pointer;
    }

    .name {
        font-size: 16px;
        font-weight: 600;
    }

    .description {
        font-size: 14px;
        color: #666;
    }
</style>
