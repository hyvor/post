<script lang="ts">
import { TextInput, SplitControl, Button, toast, confirm } from '@hyvor/design/components';
import SettingsTop from '../@components/SettingsTop.svelte';
import SettingsBody from '../@components/SettingsBody.svelte';
import ProjectSaveDiscard from '../../@components/save/ProjectSaveDiscard.svelte';
import { projectEditingStore, projectStore, updateProjectStore } from '../../../lib/stores/projectStore';
import { goto } from '$app/navigation';
import { get } from 'svelte/store';
import { deleteProject } from '../../../lib/actions/projectActions';
import { getI18n } from '../../../lib/i18n';

const I18n = getI18n();

let deleting = false;

async function onDelete() {
    const confirmation = await confirm({
        title: I18n.t('console.settings.project.deleteTitle'),
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
        .catch(() => {
            toast.error(I18n.t('console.settings.project.deleteFailed'));
        })
        .finally(() => {
            deleting = false;
            confirmation.close();
        });
}
</script>

<SettingsBody>
    <div class="project-settings-wrap">
        <SplitControl label={I18n.t('console.settings.project.name')}>
            <TextInput block bind:value={$projectEditingStore.name} />
        </SplitControl>
        <SplitControl label={I18n.t('console.settings.project.sendingEmail')}>
            <TextInput block bind:value={$projectEditingStore.default_email_username} />
        </SplitControl>
        <ProjectSaveDiscard keys={["name", "default_email_username"]} />

        <SplitControl label={I18n.t('console.settings.project.uuid')}>
            <div class="project-uuid-row">
                <TextInput block readonly bind:value={$projectStore.uuid} />
            </div>
        </SplitControl>

        <SplitControl label={I18n.t('console.settings.project.dangerZone')}>
            <Button color="red" on:click={onDelete} loading={deleting}>
                {I18n.t('console.settings.project.delete')}
            </Button>
        </SplitControl>
    </div>
</SettingsBody>

<style>
    .project-settings-wrap {
        max-width: 500px;
        margin-left: 0;
        margin-right: auto;
        text-align: left;
    }
    .project-uuid-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
