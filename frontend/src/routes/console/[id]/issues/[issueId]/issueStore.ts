import { writable } from 'svelte/store';

// whenever content is updated, we increment this id
// other components (like preview) can listen to this store to know when to update
export const contentUpdateId = writable(0);
