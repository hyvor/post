<script lang="ts">

    import Nav from "./Nav.svelte";

    interface Props {
        children?: import('svelte').Snippet;
    }

    let { children }: Props = $props();
</script>

<div class="main-inner">
    <Nav />
    <div class="content hds-box">
        {@render children?.()}
    </div>
</div>

<style>
    .main-inner {
        display: flex;
        flex: 1;
        width: 100%;
        height: 100%;
        min-height: 0;
    }

    .content {
        display: flex;
        flex-direction: column;
        margin: 15px;
        padding: 15px;
        flex: 1;
        width: 100%;
        height: calc(100vh - 30px);
        min-width: 0;
    }
</style>
