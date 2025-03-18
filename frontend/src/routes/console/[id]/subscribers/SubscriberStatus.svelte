<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { NewsletterSubscriberStatus } from '../../types';

	export let status: NewsletterSubscriberStatus;

	let color: 'green' | 'orange' | 'default';

	$: {
		color =
			status === 'subscribed' ? 'green' : status === 'unsubscribed' ? 'orange' : 'default';
	}
</script>

<Tag {color}>
	{status.charAt(0).toUpperCase() + status.slice(1)}
</Tag>
