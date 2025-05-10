<script lang="ts">
	import { onMount } from 'svelte';
	import consoleApi from '../../lib/consoleApi';
	import { IconMessage, Loader } from '@hyvor/design/components';

	let usage: Usage | undefined = $state(undefined);
	let error = $state('');

	interface Usage {
		emails: {
			this_month: number;
			last_12_months: Record<string, number>;
		};
	}

	onMount(() => {
		consoleApi
			.get<Usage>({
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
	{JSON.stringify(usage)}
{:else}
	<Loader padding={20} full />
{/if}
