<script lang="ts">
    interface Props {
        checked: boolean;
        onchange: (event: Event) => void;
    }

    let { checked, onchange }: Props = $props();
</script>

<label class="switch-wrap">
    <span class="switch">
        <input type="checkbox" bind:checked {onchange} />
        <span class="slider"></span>
    </span>
</label>

<style>
    .switch-wrap {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 36px;
        height: 22px;
        vertical-align: middle;
    }

    input {
        opacity: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--hp-accent-light);
        transition: 0.2s;
        border-radius: 30px;
    }

    .slider:hover {
        box-shadow: 0 0 0 2px var(--hp-accent-light);
    }

    .slider:before {
        position: absolute;
        content: "";
        border-radius: 50%;
        background-color: var(--hp-accent-text);
        transition: 0.1s;
        width: 16px;
        height: 16px;
        left: 3px;
        top: 3px;
        bottom: 3px;
    }

    input:checked + .slider {
        background-color: var(--hp-accent);
    }

    input:focus-visible + .slider {
        box-shadow: 0 0 0 3px var(--hp-accent-light);
    }

    .switch:active .slider,
    input:active + .slider {
        box-shadow: 0 0 0 3px var(--hp-accent-light);
    }

    input:checked + .slider:before {
        transform: translateX(14px);
    }
</style>
