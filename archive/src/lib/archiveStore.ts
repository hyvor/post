import { writable } from 'svelte/store';
import type { IssueList, Newsletter, List } from './types';

export const subdomainStore = writable<string>();
export const newsletterStore = writable({} as Newsletter);
export const issuesStore = writable([] as IssueList[]);
export const listsStore = writable<List[]>([]);
