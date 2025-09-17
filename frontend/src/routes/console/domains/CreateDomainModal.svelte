<script lang="ts">
	import {
		FormControl,
		Modal,
		SplitControl,
		TextInput,
		Validation,
		toast
	} from '@hyvor/design/components';
	import { createDomain } from '../lib/actions/domainActions';
	import DnsRecordsModal from './DnsRecordsModal.svelte';
	import type { Domain } from '../types';
	import { onMount } from 'svelte';

	interface Props {
		show?: boolean;
		onCreate: () => void;
	}

	let { show = $bindable(false), onCreate }: Props = $props();

	let domain = $state('');
	let domainError: string | null = $state(null);
	let loading = $state(false);
	let showDnsRecords = $state(false);
	let createdDomain: Domain | null = $state(null);
	let domainInput: HTMLInputElement = $state({} as HTMLInputElement);

	function validateDomain(domain: string): boolean {
		if (!domain) {
			domainError = 'Domain is required';
			return false;
		}

		// Basic domain validation
		const domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/;
		if (!domainRegex.test(domain)) {
			domainError = 'Please enter a valid domain name';
			return false;
		}

		domainError = null;
		return true;
	}

	function handleSubmit() {
		if (!validateDomain(domain)) {
			return;
		}

		loading = true;
		createDomain(domain)
			.then((newDomain) => {
				toast.success('Domain created successfully');
				show = false;
				createdDomain = newDomain;
				showDnsRecords = true;
				onCreate();
			})
			.catch((error: any) => {
				toast.error(error?.message || 'Failed to create domain');
			})
			.finally(() => {
				loading = false;
			});
	}

	$effect(() => {
		if (show && domainInput) {
			domainInput.focus();
		}
	});
</script>

<Modal
	bind:show
	title="Create Domain"
	footer={{
		cancel: {
			text: 'Cancel'
		},
		confirm: {
			text: 'Create Domain'
		}
	}}
	on:confirm={handleSubmit}
	{loading}
>
	<FormControl>
		<SplitControl label="Domain" caption="Enter the custom domain you want to add.">
			<TextInput
				placeholder="example.com"
				bind:value={domain}
				bind:input={domainInput}
				state={domainError ? 'error' : 'default'}
				block
				on:keydown={(e) => {
					if (e.key === 'Enter') {
						handleSubmit();
					}
				}}
			/>
			{#if domainError}
				<Validation type="error">{domainError}</Validation>
			{/if}
		</SplitControl>
	</FormControl>
</Modal>

{#if createdDomain}
	<DnsRecordsModal domain={createdDomain} bind:show={showDnsRecords} />
{/if}
