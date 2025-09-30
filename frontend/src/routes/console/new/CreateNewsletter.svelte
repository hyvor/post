<script lang="ts">
	import { goto } from '$app/navigation';
	import {
		Button,
		FormControl,
		Loader,
		SplitControl,
		TextInput,
		toast,
		Validation
	} from '@hyvor/design/components';
	import IconCaretLeft from '@hyvor/icons/IconCaretLeft';
	import { addUserNewsletter, userNewslettersStore } from '../lib/stores/userNewslettersStore';
	import { createNewsletter, getSubdomainAvailability } from '../lib/actions/newsletterActions';
	import { validateSubdomain } from '../lib/subdomain';

	let name = $state('');
	let subdomain = $state('');

	let subdomainEdited = false;

	let nameError: string | null = $state(null);
	let subdomainError: string | null = $state(null);
	let subdomainSuccess: string | null = $state(null);

	let isCreating = $state(false);

	let subdomainCheckTimeout: null | ReturnType<typeof setTimeout> = null;
	let subdomainCheckAbortController: AbortController | null = null;

	function checkSubdomain() {
		if (subdomainCheckTimeout) {
			clearTimeout(subdomainCheckTimeout);
		}
		if (subdomainCheckAbortController) {
			subdomainCheckAbortController.abort();
		}

		subdomainError = null;
		subdomainSuccess = null;

		if (!subdomain) return;

		subdomainCheckTimeout = setTimeout(() => {
			subdomainCheckAbortController = new AbortController();

			getSubdomainAvailability(subdomain).then((res) => {
				if (res.available) {
					subdomainSuccess = 'Subdomain is available';
				} else {
					subdomainError = 'Subdomain is already taken';
				}
			});
		}, 500);
	}

	$effect(() => {
		subdomain;
		checkSubdomain();
	});

	function handleBack() {
		if ($userNewslettersStore.length > 0) {
			goto('/console');
		} else {
			goto('/');
		}
	}

	function handleNameInput(e: any) {
		nameError = null;

		const value = e.target.value;

		if (!subdomainEdited) {
			subdomain = value
				.toLowerCase()
				.replace(/[^a-z0-9-]/g, '-')
				.replace(/-+/g, '-')
				.replace(/(^-|-$)/g, '');
		}
	}

	function handleSubdomainInput(e: any) {
		subdomainEdited = true;
		subdomainError = null;

		subdomain = e.target.value;
		subdomain = subdomain.toLowerCase();

		const subdomainValidation = validateSubdomain(subdomain);

		if (subdomainValidation) {
			subdomainError = subdomainValidation;

			if (subdomainCheckTimeout) {
				clearTimeout(subdomainCheckTimeout);
			}
		}
	}

	function handleCreate() {
		let valid = true;

		if (name.trim() === '') {
			nameError = 'Name is required';
			valid = false;
		}

		if (subdomain.trim() === '') {
			subdomainError = 'Subdomain is required';
			valid = false;
		}

		if (!valid) {
			return;
		}

		isCreating = true;

		createNewsletter(name, subdomain)
			.then((res) => {
				addUserNewsletter({ role: 'owner', newsletter: res });
				goto('/console/' + res.id);
			})
			.catch((e) => {
				toast.error(e.message);
				isCreating = false;
			});
	}
</script>

<div class="wrap">
	<div class="inner hds-box">
		<div class="back">
			<Button variant="outline" size="small" on:click={handleBack} disabled={isCreating}>
				{#snippet start()}
					<IconCaretLeft size={14} />
				{/snippet}
				Back
			</Button>
		</div>

		{#if isCreating}
			<Loader block padding={130}>Creating your newsletter...</Loader>
		{:else}
			<div class="title">Start a new newsletter</div>

			<div class="form">
				<SplitControl label="Name" caption="A name for your newsletter">
					<FormControl>
						<TextInput
							block
							bind:value={name}
							on:input={handleNameInput}
							on:keydown={(e) => e.key === 'Enter' && handleCreate()}
							maxlength="255"
							state={nameError ? 'error' : undefined}
							autofocus
						/>

						{#if nameError}
							<Validation state="error">
								{nameError}
							</Validation>
						{/if}
					</FormControl>
				</SplitControl>
				<SplitControl label="Subdomain" caption="Only a-z, 0-9, and hyphens (-)">
					<FormControl>
						<TextInput
							block
							bind:value={subdomain}
							on:input={handleSubdomainInput}
							maxlength="50"
							state={subdomainError
								? 'error'
								: subdomainSuccess
									? 'success'
									: undefined}
						></TextInput>

						{#if subdomainError}
							<Validation state="error">
								{subdomainError}
							</Validation>
						{/if}

						{#if subdomainSuccess}
							<Validation state="success">
								{subdomainSuccess}
							</Validation>
						{/if}
					</FormControl>
				</SplitControl>
			</div>

			<div class="footer">
				<Button size="large" on:click={handleCreate}>Create Newsletter</Button>
			</div>
		{/if}
	</div>
</div>

<style>
	.back {
		position: absolute;
		bottom: 100%;
		left: 0;
		padding: 15px 0;
	}

	.wrap {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		width: 100%;
		height: 100vh;
	}

	.title {
		padding: 25px;
		font-weight: 600;
		font-size: 22px;
		text-align: center;
	}

	.inner {
		width: 550px;
		max-width: 100%;
		position: relative;
	}

	.form {
		padding: 0 20px;
	}

	.footer {
		padding: 20px;
		padding-bottom: 30px;
		text-align: center;
	}
</style>
