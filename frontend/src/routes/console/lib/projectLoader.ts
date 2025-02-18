import { get } from 'svelte/store';
import type { Project } from '../types';
import consoleApi from '../lib/consoleApi';
import { projectStore } from '../lib/stores/projectStore';

interface ProjectResponse {
	project: Project;
}

// to prevent multiple requests for the same subdomain
const LOADER_PROMISES: Record<string, Promise<ProjectResponse>> = {};

export function loadProject(subdomain: string) {
	if (LOADER_PROMISES[subdomain]) {
		return LOADER_PROMISES[subdomain];
	}

	const promise = new Promise<ProjectResponse>((resolve, reject) => {
		consoleApi
			.get<ProjectResponse>({
				endpoint: '/project',
				subdomain
			})
			.then((res) => {

				projectStore.set(res.project);

				resolve(res);
			})
			.catch((err) => {
				reject(err);
			})
			.finally(() => {
				delete LOADER_PROMISES[subdomain];
			});
	});

	LOADER_PROMISES[subdomain] = promise;

	return promise;
}
