<script lang="ts">
    import { Callout } from '@hyvor/design/components';
    import IconPersonFillCheck from '@hyvor/icons/IconPersonFillCheck';
</script>

<div class="banner">
    <Callout type="success">
        {#snippet icon()}
            <IconPersonFillCheck size={18} />
        {/snippet}
        Your account has been approved!
    </Callout>
</div>

<style>
    .banner {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }
</style>
