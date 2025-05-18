<script lang="ts">
	import { userProjectsStore } from "../../lib/stores/userProjectsStore";
	import ProjectRow from "./ProjectRow.svelte";

	export let own = false;

    const projects = own ? 
					$userProjectsStore.filter(p => p.role == 'owner') :
					$userProjectsStore.filter(p => p.role != 'owner');
	
</script>

{#if projects.length}
	<div class="wrap">
		<div class="title-wrap">
			<div class="title">
				{#if own}
					Projects you own
				{:else}
					Projects you are admin
				{/if}
			</div>
			<div class="description">
				{#if own}
                	You are the owner of these projects. Your subscription applies to all of them.                
				{:else}
                    You are admin on these websites. Your subscription does not apply to them.
				{/if}
			</div>
		</div>
		{#each projects as project}
			<ProjectRow
                projectList={project}
            />   
		{/each}
	</div>
{/if}

<style>
	.wrap {
		margin-bottom: 35px;
	}
	.title-wrap {
		margin-bottom: 15px;
	}
	.title {
		font-weight: 600;
	}
	.description {
		color: var(--text-light);
		font-size: 14px;
	}
</style>
