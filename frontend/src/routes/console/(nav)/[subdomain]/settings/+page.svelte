<script lang="ts">
	import {
		TextInput,
		SplitControl,
		Button,
		toast,
		confirm,
		Callout,
		Validation,
		Switch
	} from '@hyvor/design/components';
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

	const appConfig = getAppConfig();

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

	<SplitControl label="Language Code" caption="e.g., en, ar, he, fa">
		<TextInput
			bind:value={$newsletterEditingStore.language_code}
			placeholder="en"
			maxlength={10}
		/>
	</SplitControl>

	<SplitControl
		label="Right-to-Left (RTL)"
		caption="Enable for RTL languages like Arabic, Hebrew"
	>
		<Switch bind:checked={$newsletterEditingStore.is_rtl} />
	</SplitControl>

	<SplitControl label={I18n.t('console.settings.newsletter.delete')}>
		<Button color="red" on:click={onDelete} loading={deleting}>
			{I18n.t('console.settings.newsletter.delete')}
		</Button>
	</SplitControl>
</SettingsBody>

<NewsletterSaveDiscard keys={['name', 'language_code', 'is_rtl']} />

<style>
	.newsletter-subdomain-row {
		display: flex;
		align-items: center;
		gap: 10px;
		margin-bottom: 6px;
	}
</style>
