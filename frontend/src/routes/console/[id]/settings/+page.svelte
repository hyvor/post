<script lang="ts">
	import { TextInput, SplitControl, Button, toast, confirm } from '@hyvor/design/components';
	import SettingsBody from './@components/SettingsBody.svelte';
	import NewsletterSaveDiscard from '../@components/save/NewsletterSaveDiscard.svelte';
	import { newsletterEditingStore, newsletterStore } from '../../lib/stores/newsletterStore';
	import { goto } from '$app/navigation';
	import { get } from 'svelte/store';
	import { deleteNewsletter } from '../../lib/actions/newsletterActions';
	import { getI18n } from '../../lib/i18n';

	const I18n = getI18n();

	let deleting = false;

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

		deleteNewsletter(get(newsletterStore))
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

	function copyUuid() {
		const uuid = $newsletterStore.uuid;
		navigator.clipboard.writeText(uuid).then(() => {
			toast.success(
				I18n.t('console.common.copied', {
					value: I18n.t('console.settings.newsletter.uuid')
				})
			);
		});
	}
</script>

<SettingsBody>
	<SplitControl label={I18n.t('console.settings.newsletter.name')}>
		<TextInput block bind:value={$newsletterEditingStore.name} />
	</SplitControl>

	<SplitControl
		label={I18n.t('console.settings.newsletter.uuid')}
		caption={I18n.t('console.settings.newsletter.uuidCaption')}
	>
		<div class="newsletter-uuid-row">
			<TextInput block readonly bind:value={$newsletterStore.uuid} />
		</div>
		<Button size="small" color="input" on:click={copyUuid}>
			{I18n.t('console.common.copy')}
		</Button>
	</SplitControl>

	<SplitControl label={I18n.t('console.settings.newsletter.delete')}>
		<Button color="red" on:click={onDelete} loading={deleting}>
			{I18n.t('console.settings.newsletter.delete')}
		</Button>
	</SplitControl>
</SettingsBody>

<NewsletterSaveDiscard keys={['name']} />

<style>
	.newsletter-uuid-row {
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.newsletter-uuid-row {
		margin-bottom: 6px;
	}
</style>
