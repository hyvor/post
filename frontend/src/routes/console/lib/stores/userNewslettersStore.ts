import { writable } from 'svelte/store';
import type { NewsletterList } from '../../types';

export const userNewslettersStore = writable<NewsletterList[]>([]);

export function addUserProject(project: NewsletterList) {
	userNewslettersStore.update((projects) => [...projects, project]);
}
