import { writable } from "svelte/store";
import { type ProjectStats, type Project, type List, type Issue, type ProjectList } from "../../types";

export const projectStore = writable<ProjectList>();
export const projectStatsStore = writable<ProjectStats>();
export const listStore = writable<List[]>([]);
export const issueStore = writable<Issue[]>([]);

export function updateProjectStore(project: Partial<ProjectList> | ((currentproject: ProjectList) => Partial<ProjectList>)) {
    const stores = [projectStore];
    stores.forEach(store => {
        store.update(b => {
            const val = typeof project === 'function' ? project(b) : project;
            return {...b, ...val};
        })
    })
}