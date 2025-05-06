import { writable } from "svelte/store";
import type { Project } from "../../types";

export const userProjectsOwnerStore = writable<Project[]>([]);
export const userProjectAdminStore = writable<Project[]>([]);

export function addUserProject(project: Project) {
    userProjectsOwnerStore.update((projects) => [...projects, project]);
}