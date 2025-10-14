<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';
	import { getI18n } from '../../console/lib/i18n';

	export let children: string;
	const I18n = getI18n();
</script>

<!-- href="/{I18n.getLocale()}/pricing#trialnote" -->
<Tooltip
	text="To maintain high email deliverability, we only allow paid customers to send newsletters. In the trial period, you can send test emails to verified email addresses."
>
	<a>
		{children}
	</a>
</Tooltip>

<style>
	a {
		text-decoration: underline dotted;
	}
</style>
