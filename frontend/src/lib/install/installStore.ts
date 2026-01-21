import { derived, writable } from 'svelte/store';

export const installWebsiteId = writable<null | number>();

export const installWebsiteString = derived([installWebsiteId], ([$store]) =>
	$store ? $store.toString() : 'YOUR_WEBSITE_ID'
);
