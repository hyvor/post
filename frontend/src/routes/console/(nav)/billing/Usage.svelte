<script lang="ts">
	import { onMount } from 'svelte';
	import consoleApi from '../../lib/consoleApi';
	import { IconMessage, Loader, Usage } from '@hyvor/design/components';
	import EmailsChart from './EmailsChart.svelte';
	import { getI18n } from '../../lib/i18n';

	let usage: UsageResponse | undefined = $state(undefined);
	let error = $state('');

	interface UsageResponse {
		emails: {
			limit: number;
			this_month: number;
			last_12_months: Record<string, number>;
		};
	}

	const I18n = getI18n();

	onMount(() => {
		consoleApi
			.get<UsageResponse>({
				userApi: true,
				endpoint: '/billing/usage'
			})
			.then((r) => {
				usage = r;
			})
			.catch((e) => {
				error = e.message;
			});
	});
</script>

{#if error}
	<IconMessage error message={error} />
{:else if usage}

	<Usage
		name="Emails" 
		current={usage.emails.this_month}
		limit={usage.emails.limit}
		notIncludedText={I18n.t('console.billing.usage.licenseDoesNotIncludeEmails')}
	/>

	{#if usage.emails.limit > 0 || Object.values(usage.emails.last_12_months).reduce((a, b) => a + b, 0) > 0}
		<EmailsChart data={usage.emails.last_12_months} max={usage.emails.limit} />
	{/if}

{:else}
	<Loader padding={20} full />
{/if}
