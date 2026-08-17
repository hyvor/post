import { writable } from 'svelte/store';

/**
 * an embedded console is loaded within an iframe.
 * for example, Hyvor Blogs Console embeds Hyvor Post Console within an iframe to allow accessing a certain newsletter
 *
 * What changes when embedding:
 * - only one newsletter is supported (should embed /console/:subdomain directly)
 * - cannot switch between newsletters
 * - HyvorBar is hidden
 * - padding is reduced
 * - account nav is hidden (billing and domains)
 * - nav bottom (language, dark toggle) is hidden
 * - blog nav hides:  
 *  - install
 */
export const isEmbedded = writable(false);
