import { derived, writable } from 'svelte/store';

export const installSubdomain = writable<null | string>();

export const installWebsiteId = writable<null | number | string>();
