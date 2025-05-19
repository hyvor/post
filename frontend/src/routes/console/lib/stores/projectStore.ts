import { writable } from 'svelte/store';
import {
	type ProjectStats,
	type Project,
	type List,
	type Issue,
	type UserRole,
	type SubscriberMetadataDefinition
} from '../../types';

export const projectStore = writable<Project>();
export const projectEditingStore = writable<Project>();
export const projectRoleStore = writable<UserRole>();

export const projectStatsStore = writable<ProjectStats>();
export const listStore = writable<List[]>([]);
export const subscriberMetadataDefinitionStore = writable<SubscriberMetadataDefinition[]>();
export const issueStore = writable<Issue[]>([]);

export function setProjectStore(project: Project) {
	projectStore.set(project);
	projectEditingStore.set({ ...project });
}

export function updateProjectStore(
	project: Partial<Project> | ((currentproject: Project) => Partial<Project>)
) {
	const stores = [projectStore, projectEditingStore];

	stores.forEach((store) => {
		store.update((b) => {
			const val = typeof project === 'function' ? project(b) : project;
			return { ...b, ...val };
		});
	});
}
