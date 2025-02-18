import { writable } from "svelte/store";
import type { Project } from "../../types";

export const projectListStore = writable<Project[]>([]);