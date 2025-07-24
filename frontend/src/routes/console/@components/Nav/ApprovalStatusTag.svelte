<script lang="ts">
    import { Tag } from '@hyvor/design/components';
    import type {ApprovalStatus, IssueStatus} from '../../types';
    import IconCheckCircle from '@hyvor/icons/IconCheckCircle';
    import IconHourglassSplit from '@hyvor/icons/IconHourglassSplit';
    import IconExclamationCircle from '@hyvor/icons/IconExclamationCircle';
    import IconXCircle from "@hyvor/icons/IconXCircle";

    interface Props {
        status: ApprovalStatus;
    }

    let { status }: Props = $props();

    // let color: 'blue' | 'orange' | 'green' | 'red';
    // let text: string;
    let color = $state<'blue' | 'orange' | 'green' | 'red'>('blue');
    let text = $state<string|undefined>(undefined);

    $effect(() => {
        if (status === 'pending') {
            color = 'orange';
        } else if (status === 'reviewing') {
            color = 'blue';
        } else if (status === 'approved') {
            color = 'green';
        } else if (status === 'rejected') {
            color = 'red';
        }
    });

    $effect(() => {
        text = status.charAt(0).toUpperCase() + status.slice(1);
    });
</script>

<Tag {color} size="x-small">
    {text}
    {#snippet end()}
        {#if status === 'pending'}
            <IconExclamationCircle size={10} />
        {:else if status === 'reviewing'}
            <IconHourglassSplit size={10} />
        {:else if status === 'approved'}
            <IconCheckCircle size={10} />
        {:else if status === 'rejected'}
            <IconXCircle size={10} />
        {/if}
    {/snippet}
</Tag>
