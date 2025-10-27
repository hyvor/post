<script lang="ts">
    import {Tooltip} from '@hyvor/design/components';

    interface Props {
        children?: import('svelte').Snippet;
    }

    let {children}: Props = $props();
</script>

<Tooltip
        text="To maintain high email deliverability, we only allow paid customers to send newsletters. In the trial period, you can send test emails to verified email addresses."
>
    <a>
        {@render children?.()}
    </a>
</Tooltip>

<style>
    a {
        text-decoration: underline dotted;
    }
</style>
