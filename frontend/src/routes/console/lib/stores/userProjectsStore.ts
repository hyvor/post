import { writable } from 'svelte/store';
import type { ProjectList } from '../../types';

export const userProjectsStore = writable<ProjectList[]>([]);

export function addUserProject(project: ProjectList) {
	userProjectsStore.update((projects) => [...projects, project]);
}
