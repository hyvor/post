<script lang="ts">
import { TextInput, SplitControl, Button, toast, confirm } from '@hyvor/design/components';
import SettingsTop from '../@components/SettingsTop.svelte';
import SettingsBody from '../@components/SettingsBody.svelte';
import ProjectSaveDiscard from '../../@components/save/ProjectSaveDiscard.svelte';
import { projectEditingStore, projectStore, updateProjectStore } from '../../../lib/stores/projectStore';
import { goto } from '$app/navigation';
import { get } from 'svelte/store';
import { deleteProject } from '../../../lib/actions/projectActions';

let deleting = false;

async function onDelete() {
    const confirmation = await confirm({
        title: 'Delete Project',
        content: 'Are you sure you want to delete this project? This action cannot be undone.',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        danger: true
    });
    if (!confirmation) return;
    confirmation.loading();
    deleting = true;
    deleteProject(get(projectStore))
        .then(() => {
            toast.success('Project deleted');
            goto('/');
        })
        .catch(() => {
            toast.error('Failed to delete project');
        })
        .finally(() => {
            deleting = false;
            confirmation.close();
        });
}
</script>

<SettingsBody>
    <div class="project-settings-wrap">
        <SplitControl label="Project Name">
            <TextInput block bind:value={$projectEditingStore.name} />
        </SplitControl>
        <ProjectSaveDiscard keys={["name"]} />

        <SplitControl label="Project UUID">
            <div class="project-uuid-row">
                <TextInput block readonly bind:value={$projectStore.uuid} />
            </div>
        </SplitControl>

        <SplitControl label="Danger Zone">
            <Button color="red" on:click={onDelete} loading={deleting}>
                Delete Project
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
    .project-uuid {
        font-size: 15px;
        background: var(--accent-lightest);
        padding: 2px 8px;
        border-radius: 4px;
    }
</style>
