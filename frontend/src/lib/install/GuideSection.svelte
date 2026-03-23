<script lang="ts">
    import IconChat from '@hyvor/icons/IconChat';

    interface Props {
        title?: string;
        children?: import('svelte').Snippet;
    }

    let { title, children }: Props = $props();

    // Create a slug for the ID from the title
    let id = $derived(title?.toLowerCase().replace(/\s+/g, '-'));
</script>

<div
    class="guide-section"
    style:--guide-color="var(--green)"
    style:--guide-color-light="var(--green-light)"
    style:--guide-color-dark="var(--green-dark)"
>
    {#if title}
        <h2 {id}>
            {title}
            <span class="icon">
                <IconChat size={25} />
            </span>
        </h2>
    {/if}
    
    {@render children?.()}
</div>

<style lang="scss">
    .guide-section {
        padding: 30px 50px 30px;
        margin: 0 -50px;
        /* Hardcoded the background mix to use green-light */
        background-color: color-mix(in srgb, var(--green-light) 15%, transparent);
    }

    h2 {
        text-align: center;
        padding-top: 35px !important;
        margin-top: 0 !important;
        margin-bottom: 30px !important;
        font-size: 30px !important;
    }

    h2 :global(.heading-anchor-link) {
        display: none !important;
    }

    .icon {
        margin-left: 6px;
        vertical-align: middle;
        color: var(--guide-color);
    }

    .guide-section {
        :global(ol) {
            margin: 1em 0 0 !important;
            padding: 0 !important;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            counter-reset: steps;
            list-style: none;
        }
        :global(ol > li) {
            padding: 25px 0 0 25px !important;
            margin: 16px 0 0 !important;
            position: relative;
            border-top: 1px solid var(--guide-color-light);
        }
        :global(ol > li:before) {
            counter-increment: steps;
            content: counter(steps);
            position: absolute;
            left: 0;
            top: -10px;
            width: 20px;
            height: 20px;
            background-color: var(--guide-color);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
            font-weight: 600;
            font-size: 10px;
            color: #fff;
        }
        :global(ol > li:after) {
            content: '';
            position: absolute;
            left: -4px;
            top: -14px;
            width: 28px;
            height: 28px;
            background-color: var(--guide-color-light);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 0;
        }
    }

    @media (max-width: 992px) {
        .guide-section {
            margin: 0 -25px;
            padding: 30px 25px;
        }
    }
</style>