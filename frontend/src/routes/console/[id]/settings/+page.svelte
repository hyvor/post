<script lang="ts">
	import { TextInput, SplitControl, Button, toast, confirm } from '@hyvor/design/components';
	import SettingsBody from './@components/SettingsBody.svelte';
	import ProjectSaveDiscard from '../@components/save/ProjectSaveDiscard.svelte';
	import { projectEditingStore, projectStore } from '../../lib/stores/projectStore';
	import { goto } from '$app/navigation';
	import { get } from 'svelte/store';
	import { deleteProject } from '../../lib/actions/projectActions';
	import { getI18n } from '../../lib/i18n';

	const I18n = getI18n();

	let deleting = false;

	async function onDelete() {
		const confirmation = await confirm({
			title: I18n.t('console.settings.project.delete'),
			content: I18n.t('console.settings.project.deleteContent'),
			confirmText: I18n.t('console.settings.project.delete'),
			cancelText: I18n.t('console.common.cancel'),
			danger: true
		});

		if (!confirmation) return;
		confirmation.loading();
		deleting = true;

		deleteProject(get(projectStore))
			.then(() => {
				toast.success(I18n.t('console.settings.project.deleted'));
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
		const uuid = $projectStore.uuid;
		navigator.clipboard.writeText(uuid).then(() => {
			toast.success(
				I18n.t('console.common.copied', {
					value: I18n.t('console.settings.project.uuid')
				})
			);
		});
	}
</script>

<SettingsBody>
	<SplitControl label={I18n.t('console.settings.project.name')}>
		<TextInput block bind:value={$projectEditingStore.name} />
	</SplitControl>

	<SplitControl
		label={I18n.t('console.settings.project.uuid')}
		caption={I18n.t('console.settings.project.uuidCaption')}
	>
		<div class="project-uuid-row">
			<TextInput block readonly bind:value={$projectStore.uuid} />
		</div>
		<Button size="small" color="input" on:click={copyUuid}>
			{I18n.t('console.common.copy')}
		</Button>
	</SplitControl>

	<SplitControl label={I18n.t('console.settings.project.delete')}>
		<Button color="red" on:click={onDelete} loading={deleting}>
			{I18n.t('console.settings.project.delete')}
		</Button>
	</SplitControl>
</SettingsBody>

<ProjectSaveDiscard keys={['name']} />

<style>
	.project-uuid-row {
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.project-uuid-row {
		margin-bottom: 6px;
	}
</style>
