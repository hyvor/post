<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { ApprovalStatus, IssueStatus } from '../../types';
	import IconCheckCircle from '@hyvor/icons/IconCheckCircle';
	import IconHourglassSplit from '@hyvor/icons/IconHourglassSplit';
	import IconXCircle from '@hyvor/icons/IconXCircle';
	import IconCardChecklist from '@hyvor/icons/IconCardChecklist';

	interface Props {
		status: ApprovalStatus;
		size?: 'x-small' | 'small' | 'medium' | 'large';
		iconSize?: number;
	}

	let { status, size = 'x-small', iconSize = 10 }: Props = $props();

	let color = $state<'blue' | 'orange' | 'green' | 'red'>('blue');
	let text = $state<string | undefined>(undefined);

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

<Tag {color} {size}>
	{text}
	{#snippet end()}
		{#if status === 'pending'}
			<IconCardChecklist size={iconSize} />
		{:else if status === 'reviewing'}
			<IconHourglassSplit size={iconSize} />
		{:else if status === 'approved'}
			<IconCheckCircle size={iconSize} />
		{:else if status === 'rejected'}
			<IconXCircle size={iconSize} />
		{/if}
	{/snippet}
</Tag>
