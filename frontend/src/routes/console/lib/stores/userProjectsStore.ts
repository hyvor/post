import { writable } from "svelte/store";
import type { Project } from "../../types";
import type ProjectList from "../../@components/Nav/ProjectList.svelte";

export const userProjectsStore = writable<ProjectList[]>([]);

export function addUserProject(project: ProjectList) {
    userProjectsStore.update((projects) => [...projects, project]);
}