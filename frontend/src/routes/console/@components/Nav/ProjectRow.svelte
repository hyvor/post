<script lang="ts">
	import { goto } from '$app/navigation';
	import { Tag } from '@hyvor/design/components';
	import type { Project } from '../../types';
	import { projectStore } from '../../lib/stores/projectStore';
	import { loadProject } from '../../lib/projectLoader';
	import RoleTag from './RoleTag.svelte';
	import { selectingProject } from '../../lib/stores/consoleStore';

	export let project: Project;

	function onClick() {
		projectStore.set(project);
        goto(`/console/${project.id}`);
		loadProject(String(project.id));
		selectingProject.set(false);
	}
</script>

<div
	class="wrap"
	role="button"
	on:click={onClick}
	on:keyup={(e) => e.key === 'Enter' && onClick()}
	tabindex="0"
>
	<div class="name-id">
		<div class="name">{project.name}</div>
		<div class="id">
			<span class="id-tag">ID: </span><Tag size="x-small"
				><strong>{project.id}</strong></Tag
			>
		</div>
	</div>

    <div class="role">
		<RoleTag role={project.current_user.role} />
	</div>

	<div class="right">&rarr;</div>

</div>

<style lang="scss">
	.wrap {
		padding: 15px 25px;
        background-color: var(--accent-light-mid);
		cursor: pointer;
		border-radius: var(--box-radius);
		display: flex;
		align-items: center;
		position: relative;
		overflow: hidden;
		margin-bottom: 10px;
	}
    .wrap:hover {
        background-color: var(--accent-light);
    }
	.name-id {
		flex: 2;
	}
	.usage {
		flex: 3;
		display: flex;
		align-items: center;
		gap: 14px;
		.usage-usage {
			text-transform: lowercase;
		}
	}
	.td-tag {
		font-size: 14px;
		color: var(--text-light);
	}
	.name {
		font-weight: 600;
	}
	.id-tag {
		font-size: 12px;
		color: var(--text-light);
	}
	.role {
		margin-right: 15px;
	}
	.overlay {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		opacity: 0.05;
		transition: 0.2s opacity;
		z-index: 0;
	}
	.wrap:hover .overlay {
		opacity: 0.1;
	}

	@media (max-width: 768px) {
		.wrap {
			display: grid;
			grid-template-columns: repeat(5, 1fr);
			grid-template-rows: repeat(3, min-content);
			grid-row-gap: 10px;
		}
		.right {
			grid-area: 1 / 5 / 4 / 6;
			text-align: center;
		}
		.name-id {
			grid-area: 1 / 1 / 1 / 5;
		}
		.usage {
			grid-area: 2 / 1 / 2 / 5;
			flex-direction: column;
			align-items: flex-start;
			gap: 6px;
		}
		.role {
			grid-area: 3 / 1 / 3 / 2;
		}
	}
</style>
