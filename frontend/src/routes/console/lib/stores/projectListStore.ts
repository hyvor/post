import { writable } from "svelte/store";
import type { Project } from "../../types";

export const projectListStore = writable<Project[]>([]);

export function addToProjectList(project: Project) {
    projectListStore.update((projects) => [...projects, project]);
}