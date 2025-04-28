<script lang="ts">
	import { FormControl, Modal, SplitControl, TextInput, Validation, toast } from '@hyvor/design/components';
	import { createDomain } from '../lib/actions/domainActions';


	export let show = false;
	export let onCreate: () => void;

	let domain = '';
	let domainError: string | null = null;
	let loading = false;

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
			.then(() => {
				toast.success('Domain created successfully');
				show = false;
				onCreate();
			})
			.catch((error: any) => {
				toast.error(error?.message || 'Failed to create domain');
			})
			.finally(() => {
				loading = false;
			});
	}
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
		<SplitControl 
			label="Domain"
			caption="Enter the domain name you want to use for your newsletter"
		>
			<TextInput
				placeholder="example.com"
				bind:value={domain}
				state={domainError ? 'error' : 'default'}
			/>
			{#if domainError}
				<Validation type="error">{domainError}</Validation>
			{/if}
		</SplitControl>
	</FormControl>
</Modal> 