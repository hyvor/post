import { writable } from "svelte/store";

export const LOCAL_STORAGE_KEY = 'console-temp-subdomain';

export const isTempStore = writable(false);

export const tempSubdomainStore = writable<string | null>(null);