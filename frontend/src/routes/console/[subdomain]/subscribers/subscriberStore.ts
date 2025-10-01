import { writable } from 'svelte/store';

export const selectedSubscriberIds = writable<number[]>([]); 