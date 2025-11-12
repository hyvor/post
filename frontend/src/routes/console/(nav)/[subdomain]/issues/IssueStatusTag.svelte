<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { IssueStatus } from '../../../types';

	export let status: IssueStatus;
	export let size: 'small' | 'medium' | 'large' = 'medium';

	let color: 'default' | 'blue' | 'orange' | 'green' | 'red';

	$: {
		if (status === 'draft') {
			color = 'default';
		} else if (status === 'scheduled') {
			color = 'blue';
		} else if (status === 'sending') {
			color = 'orange';
		} else if (status === 'sent') {
			color = 'green';
		} else if (status === 'failed') {
			color = 'red';
		}
	}

	$: text = status.charAt(0).toUpperCase() + status.slice(1);
</script>

<Tag {color} {size}>{text}</Tag>
