import { writable } from "svelte/store";
import type { NewsletterList } from "../../types";

export const userNewslettersStore = writable<NewsletterList[]>([]);

export function addUserNewsletter(newsletter: NewsletterList) {
  userNewslettersStore.update((newsletters) => [...newsletters, newsletter]);
}
