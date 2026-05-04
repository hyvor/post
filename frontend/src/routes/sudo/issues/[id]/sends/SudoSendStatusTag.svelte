<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { SendStatus } from '../../../types';

	interface Props {
		status: SendStatus;
	}

	let { status }: Props = $props();

	const color = $derived<'green' | 'default' | 'red'>(
		status === 'sent' ? 'green' : status === 'failed' ? 'red' : 'default'
	);
	const text = $derived(status.charAt(0).toUpperCase() + status.slice(1));
</script>

<Tag {color} size="small">
	{text}
</Tag>
