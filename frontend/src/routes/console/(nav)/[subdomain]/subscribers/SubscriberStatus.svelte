<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { NewsletterSubscriberStatus } from '../../../types';

	interface Props {
		status: NewsletterSubscriberStatus;
		size?: 'small' | 'medium';
	}

	let { status, size = 'medium' }: Props = $props();

	let color: 'green' | 'orange' | 'default' = $derived(
		status === 'subscribed' ? 'green' : 'default'
	);
</script>

<Tag {color} {size}>
	{status.charAt(0).toUpperCase() + status.slice(1)}
</Tag>
