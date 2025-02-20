import { get } from 'svelte/store';
import type { Project } from '../types';
import consoleApi from '../lib/consoleApi';
import { projectStore } from '../lib/stores/projectStore';

interface ProjectResponse {
	project: Project;
}

// to prevent multiple requests for the same subdomain
const LOADER_PROMISES: Record<string, Promise<ProjectResponse>> = {};

export function loadProject(projectId: string) {
	if (LOADER_PROMISES[projectId]) {
		return LOADER_PROMISES[projectId];
	}

	const promise = new Promise<ProjectResponse>((resolve, reject) => {
		consoleApi
			.get<ProjectResponse>({
				endpoint: 'init/project',
				userApi: true,
				projectId
			})
			.then((res) => {
				console.log(res)
				projectStore.set(res.project);

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
