<script lang="ts">
    interface Props {
        as?: 'button' | 'a';
        size?: 'x-small' | 'small' | 'medium' | 'large';
        color?: string;
        backgroundColor?: string;
        block?: boolean;
        align?: 'start' | 'center';
        button?: any;
        start?: import('svelte').Snippet;
        children?: import('svelte').Snippet;
        end?: import('svelte').Snippet;
        action?: import('svelte').Snippet;

        [key: string]: any;

        onkeyup?: (event: KeyboardEvent) => void;
        onkeydown?: (event: KeyboardEvent) => void;
        onkeypress?: (event: KeyboardEvent) => void;
        onfocus?: (event: FocusEvent) => void;
        onblur?: (event: FocusEvent) => void;
        onclick?: (event: MouseEvent) => void;
        onmouseover?: (event: MouseEvent) => void;
        onmouseenter?: (event: MouseEvent) => void;
        onmouseleave?: (event: MouseEvent) => void;
        onchange?: (event: Event) => void;
    }

    let {
        as = 'button',
        size = 'medium',
        color = 'var(--hp-accent-text)',
        backgroundColor = 'var(--hp-accent)',
        block = false,
        align = 'center',
        button = $bindable({} as HTMLButtonElement | HTMLAnchorElement),
        start,
        children,
        end,
        action,

        onkeyup,
        onkeydown,
        onkeypress,
        onfocus,
        onblur,
        onclick,
        onmouseover,
        onmouseenter,
        onmouseleave,
        onchange,

        ...rest
    }: Props = $props();
</script>

<svelte:element
    this={as}
    class="button {size} {align}"
    class:block
    style="color: {color}; background-color: {backgroundColor};"

    onkeyup={onkeyup}
    onkeydown={onkeydown}
    onkeypress={onkeypress}
    onfocus={onfocus}
    onblur={onblur}
    onclick={onclick}
    onmouseover={onmouseover}
    onmouseenter={onmouseenter}
    onmouseleave={onmouseleave}
    onchange={onchange}

    role="button"
    tabindex="0"
    bind:this={button}
    {...rest}
>
	<span class="button-content">
		{#if start}
			<span class="slot start">{@render start?.()}</span>
		{/if}

        {@render children?.()}

        {#if end}
			<span class="slot end">{@render end?.()}</span>
		{/if}
	</span>

    {#if action}
		<span class="action">
			{@render action?.()}
		</span>
    {/if}
</svelte:element>

<style lang="scss">
    .slot.start {
        margin-right: 6px;
        display: inline-flex;
        align-items: center;

        &:empty {
            margin-right: 0;
        }
    }

    .slot.end {
        margin-left: 6px;
        display: inline-flex;
        align-items: center;

        &:empty {
            margin-left: 0;
        }
    }

    .button {
        position: relative;
        display: inline-flex;
        align-items: center;
        font-weight: 600;
        font-size: 14px;
        border-radius: 20px;
        cursor: pointer;

        --local-hover-shadow-size: 2.5px;
        --local-hover-shadow-color: var(--hp-accent-light);

        &:active {
            --local-hover-shadow-size: 4px;
        }

        &.block {
            display: flex;
            width: 100%;
        }

        &:hover {
            box-shadow: 0 0 0 var(--local-hover-shadow-size) var(--local-hover-shadow-color);
        }

        &:focus-visible {
            outline: none;
            box-shadow: 0 0 0 calc(var(--local-hover-shadow-size) + 1px) var(--local-hover-shadow-color);
        }
    }

    .button-content {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 1;
    }

    .button.start .button-content {
        justify-content: flex-start;
    }

    .action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 6px;
    }

    /* Sizes */
    .button.x-small {
        height: 20px;
        padding: 0 8px;
        font-size: 12px;
        --local-hover-shadow-size: 1px;

        &:active {
            --local-hover-shadow-size: 2px;
        }
    }

    .button.small {
        height: 26px;
        padding: 0 12px;

        --local-hover-shadow-size: 2px;

        &:active {
            --local-hover-shadow-size: 3px;
        }

        .slot.start {
            margin-right: 4px;
        }

        .slot.end {
            margin-left: 4px;
        }
    }

    .button.medium {
        height: 30px;
        padding: 0 14px;
    }

    .button.large {
        height: 36px;
        padding: 0 20px;
        --local-hover-shadow-size: 3px;

        &:active {
            --local-hover-shadow-size: 5px;
        }
    }

    .button {
        transition: 0.2s box-shadow;
        --local-hover-shadow-color: var(--hp-accent-light);
    }

    .button.invisible {
        background-color: transparent;
        color: var(--text);
        transition: 0.2s background-color;

        &:hover {
            background-color: var(--hp-accent-light);
            box-shadow: none !important;
        }

        /*styles for disabled state*/
        &[disabled] {
            background-color: transparent;
            color: var(--hp-accent-text);
            box-shadow: none !important;
            cursor: not-allowed;
            opacity: 0.2;

            &:hover {
                background-color: transparent;
                box-shadow: none !important;
                color: var(--hp-accent-text);
            }
        }
    }

    /*Styles for button disabled state*/
    .button[disabled] {
        cursor: not-allowed;
        opacity: 0.2;
        box-shadow: none !important;

        &:hover {
            box-shadow: none !important;
        }
    }
</style>
