import { get } from 'svelte/store';
import type { List, Project, ProjectStats } from '../types';
import consoleApi from '../lib/consoleApi';
import { issueStore, listStore, projectStatsStore, setProjectStore } from './stores/projectStore';

interface ProjectResponse {
	project: Project;
	stats: ProjectStats;
	lists: List[];
}

// to prevent multiple requests for the same subdomain
const LOADER_PROMISES: Record<string, Promise<ProjectResponse>> = {};

export function loadProject(projectId: string) {
	if (LOADER_PROMISES[projectId] !== undefined) {
		return LOADER_PROMISES[projectId];
	}

	const promise = new Promise<ProjectResponse>((resolve, reject) => {
		consoleApi
			.get<ProjectResponse>({
				endpoint: 'init/project',
				userApi: true,
				projectId: projectId
			})
			.then((res) => {
				
				setProjectStore(res.project);
				projectStatsStore.set(res.stats);
				listStore.set(res.lists);

				issueStore.set([]); 

				resolve(res);
			})
			.catch((err) => {
				reject(err);
			})
			.finally(() => {
				delete LOADER_PROMISES[projectId];
			});
	});

	LOADER_PROMISES[projectId] = promise;

	return promise;
}
