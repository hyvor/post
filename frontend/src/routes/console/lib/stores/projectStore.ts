import { writable } from "svelte/store";
import { type ProjectStats, type Project, type List } from "../../types";

export const projectStore = writable<Project>();
export const projectStatsStore = writable<ProjectStats>();
export const listStore = writable<List[]>([]);

export function updateProjectStore(project: Partial<Project> | ((currentproject: Project) => Partial<Project>)) {
    const stores = [projectStore];
    stores.forEach(store => {
        store.update(b => {
            const val = typeof project === 'function' ? project(b) : project;
            return {...b, ...val};
        })
    })
}