import { writable } from "svelte/store";
import type { IssueList, Newsletter } from "./newsletterPageTypes";

export const newsletterStore = writable({} as Newsletter);
export const issuesStore = writable([] as IssueList[]);