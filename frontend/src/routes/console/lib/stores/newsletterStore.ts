import { writable } from 'svelte/store';
import {
	type NewsletterStats,
	type Newsletter,
	type List,
	type Issue,
	type UserRole,
	type SubscriberMetadataDefinition
} from '../../types';

export const projectStore = writable<Newsletter>();
export const projectEditingStore = writable<Newsletter>();
export const projectRoleStore = writable<UserRole>();

export const projectStatsStore = writable<NewsletterStats>();
export const listStore = writable<List[]>([]);
export const subscriberMetadataDefinitionStore = writable<SubscriberMetadataDefinition[]>();
export const issueStore = writable<Issue[]>([]);

export function setProjectStore(project: Newsletter) {
	projectStore.set(project);
	projectEditingStore.set({ ...project });
}

export function updateProjectStore(
	project: Partial<Newsletter> | ((currentproject: Newsletter) => Partial<Newsletter>)
) {
	const stores = [projectStore, projectEditingStore];

	stores.forEach((store) => {
		store.update((b) => {
			const val = typeof project === 'function' ? project(b) : project;
			return { ...b, ...val };
		});
	});
}
