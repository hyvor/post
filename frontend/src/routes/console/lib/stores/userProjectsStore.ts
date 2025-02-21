import { writable } from "svelte/store";
import type { Project } from "../../types";

export const userProjectsStore = writable<Project[]>([]);

export function addUserProject(project: Project) {
    userProjectsStore.update((projects) => [...projects, project]);
}