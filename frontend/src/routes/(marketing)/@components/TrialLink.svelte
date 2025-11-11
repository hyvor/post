<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';

	export let children: string;
</script>

<Tooltip
	text="To maintain high email deliverability, we only allow paid customers to send newsletters. In the trial period, you can send test emails to verified email addresses."
>
	<trial>
		{children}
	</trial>
</Tooltip>

<style>
	trial {
		text-decoration: underline dotted;
	}
</style>
