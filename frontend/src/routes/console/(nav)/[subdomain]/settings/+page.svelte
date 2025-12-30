<script lang="ts">
	import {
		TextInput,
		SplitControl,
		Button,
		toast,
		confirm,
		Callout,
		Validation
	} from '@hyvor/design/components';
	import IconX from '@hyvor/icons/IconX';
	import SettingsBody from './@components/SettingsBody.svelte';
	import NewsletterSaveDiscard from '../@components/save/NewsletterSaveDiscard.svelte';
	import { newsletterEditingStore, newsletterStore } from '../../../lib/stores/newsletterStore';
	import { goto } from '$app/navigation';
	import { deleteNewsletter, updateNewsletter } from '../../../lib/actions/newsletterActions';
	import { getI18n } from '../../../lib/i18n';
	import { consoleUrlWithNewsletter } from '../../../lib/consoleUrl';
	import { getAppConfig } from '../../../lib/stores/consoleStore';
	import { getNewsletterArchiveUrlFromSubdomain } from '../../../lib/archive';
	import { validateSubdomain } from '../../../lib/subdomain';

	const I18n = getI18n();

	let deleting = $state(false);
	let subdomainError = $state('');
	let subdomainUpdating = $state(false);
	let newDomain = $state('');

	const appConfig = getAppConfig();

	function normalizeDomain(input: string): string {
		let domain = input.trim();
		// Remove protocol
		domain = domain.replace(/^https?:\/\//, '');
		// Remove path, query, and fragment
		domain = domain.split('/')[0].split('?')[0].split('#')[0];
		return domain.toLowerCase();
	}

	function addDomain() {
		const domain = normalizeDomain(newDomain);
		if (!domain) return;

		const currentDomains = $newsletterEditingStore.allowed_domains || [];
		if (currentDomains.includes(domain)) {
			toast.error('Domain already added');
			return;
		}

		$newsletterEditingStore.allowed_domains = [...currentDomains, domain];
		newDomain = '';
	}

	function removeDomain(domain: string) {
		$newsletterEditingStore.allowed_domains = (
			$newsletterEditingStore.allowed_domains || []
		).filter((d) => d !== domain);
	}

	function handleDomainKeydown(e: KeyboardEvent) {
		if (e.key === 'Enter') {
			e.preventDefault();
			addDomain();
		}
	}

	async function onDelete() {
		const confirmation = await confirm({
			title: I18n.t('console.settings.newsletter.delete'),
			content: I18n.t('console.settings.newsletter.deleteContent'),
			confirmText: I18n.t('console.settings.newsletter.delete'),
			cancelText: I18n.t('console.common.cancel'),
			danger: true
		});

		if (!confirmation) return;
		confirmation.loading();
		deleting = true;

		deleteNewsletter()
			.then(() => {
				toast.success(I18n.t('console.settings.newsletter.deleted'));
				goto('/');
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				deleting = false;
				confirmation.close();
			});
	}

	function handleSubdomainInput() {
		subdomainError = '';

		const subdomain = $newsletterEditingStore.subdomain;

		const subdomainValidation = validateSubdomain(subdomain);

		if (subdomainValidation) {
			subdomainError = subdomainValidation;
		}
	}

	function copySubdomain() {
		const subdomain = $newsletterStore.subdomain;
		navigator.clipboard.writeText(subdomain).then(() => {
			toast.success(
				I18n.t('console.common.copied', {
					value: I18n.t('console.settings.newsletter.subdomain')
				})
			);
		});
	}

	function handleChange() {
		subdomainUpdating = true;

		updateNewsletter({ subdomain: $newsletterEditingStore.subdomain }, true)
			.then(() => {
				toast.success('Subdomain updated');
				goto(consoleUrlWithNewsletter(''));
			})
			.catch((e) => {
				toast.error(e.message);
			})
			.finally(() => {
				subdomainUpdating = false;
			});
	}
</script>

<SettingsBody>
	<SplitControl label={I18n.t('console.settings.newsletter.name')}>
		<TextInput block bind:value={$newsletterEditingStore.name} />
	</SplitControl>

	<SplitControl
		label={I18n.t('console.settings.newsletter.subdomain')}
		caption={I18n.t('console.settings.newsletter.subdomainCaption')}
	>
		<div class="newsletter-subdomain-row">
			<TextInput
				block
				bind:value={$newsletterEditingStore.subdomain}
				oninput={handleSubdomainInput}
				maxlength={50}
			/>
		</div>

		{#if $newsletterEditingStore.subdomain !== $newsletterStore.subdomain}
			{#if subdomainError}
				<Validation state="error">{subdomainError}</Validation>
			{:else}
				<Callout type="warning" style="margin-top:10px">
					By changing the subdomain,
					<ul>
						<li>
							The archive site URL will change to <strong
								>{getNewsletterArchiveUrlFromSubdomain(
									$newsletterEditingStore.subdomain
								).replace(/https?:\/\//, '')}</strong
							>
						</li>
						<li>
							The system <a
								class="hds-link"
								href={consoleUrlWithNewsletter('/settings/sending-profiles')}
								>sending profile</a
							>
							email address will change to
							<strong
								>{$newsletterEditingStore.subdomain}@{appConfig.app
									.default_email_domain}</strong
							>
						</li>
					</ul>

					<div style="text-align:right">
						<Button
							variant="outline"
							onclick={() => {
								$newsletterEditingStore.subdomain = $newsletterStore.subdomain;
							}}
							disabled={subdomainUpdating}>Cancel</Button
						>
						<Button onclick={handleChange} disabled={subdomainUpdating}
							>Change subdomain</Button
						>
					</div>
				</Callout>
			{/if}
		{:else}
			<Button size="small" color="input" on:click={copySubdomain}>
				{I18n.t('console.common.copy')}
			</Button>
		{/if}
	</SplitControl>

	<SplitControl label="Allowed Domains">
		<div class="domains-input-container">
			{#each $newsletterEditingStore.allowed_domains || [] as domain (domain)}
				<span class="domain-tag">
					https://{domain}
					<button class="domain-remove" on:click={() => removeDomain(domain)}>
						<IconX size={10} />
					</button>
				</span>
			{/each}
			<input
				type="text"
				class="domain-inline-input"
				bind:value={newDomain}
				placeholder="Add a domain ↵"
				on:keydown={handleDomainKeydown}
			/>
		</div>
	</SplitControl>

	<SplitControl label={I18n.t('console.settings.newsletter.delete')}>
		<Button color="red" on:click={onDelete} loading={deleting}>
			{I18n.t('console.settings.newsletter.delete')}
		</Button>
	</SplitControl>
</SettingsBody>

<NewsletterSaveDiscard keys={['name', 'allowed_domains']} />

<style>
	.newsletter-subdomain-row {
		display: flex;
		align-items: center;
		gap: 10px;
		margin-bottom: 6px;
	}

	.domains-input-container {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 8px;
		padding: 8px 12px;
		border: 1px solid var(--input-border);
		border-radius: var(--input-radius);
		background: var(--input-background-filled);
		min-height: 42px;
	}

	.domains-input-container:focus-within {
		border-color: var(--accent);
		box-shadow: 0 0 0 2px var(--accent-light);
	}

	.domain-tag {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 4px 8px;
		background: var(--box-background);
		border: 1px solid var(--border);
		border-radius: 20px;
		font-size: 14px;
		color: var(--text);
	}

	.domain-remove {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		padding: 0;
		border: none;
		background: none;
		cursor: pointer;
		color: var(--text-light);
		transition: color 0.15s;
	}

	.domain-remove:hover {
		color: var(--text);
	}

	.domain-inline-input {
		flex: 1;
		min-width: 120px;
		border: none;
		background: transparent;
		outline: none;
		font-size: 14px;
		font-family: inherit;
		color: var(--text);
	}

	.domain-inline-input::placeholder {
		color: var(--text-light);
	}
</style>
