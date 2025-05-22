import { writable } from "svelte/store";
import type { Newsletter } from "./newsletterPageTypes";

export const newsletterStore = writable({} as Newsletter);