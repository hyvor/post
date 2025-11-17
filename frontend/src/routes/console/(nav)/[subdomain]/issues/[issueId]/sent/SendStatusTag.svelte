<script lang="ts">
    import {Tag} from "@hyvor/design/components";
    import type {SendStatus} from "../../../../../types";

    interface Props {
        status: SendStatus
    }

    let {status}: Props = $props();

    let color = $state<'green' | 'default' | 'red'>('default');
    let text = $state<string | undefined>(undefined);

    $effect(() => {
        if (status === 'sent') {
            color = 'green';
        } else if (status === 'pending') {
            color = 'default';
        } else if (status === 'failed') {
            color = 'red';
        }
    })

    $effect(() => {
        text = status.charAt(0).toUpperCase() + status.slice(1);
    });
</script>

<Tag {color} size="small">
    {text}
</Tag>