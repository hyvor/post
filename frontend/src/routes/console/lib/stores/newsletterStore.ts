import { writable } from 'svelte/store';
import {
	type NewsletterStats,
	type Newsletter,
	type List,
	type Issue,
	type UserRole,
	type SubscriberMetadataDefinition,
	type SendingProfile
} from '../../types';

export const newsletterStore = writable<Newsletter>();
export const newsletterEditingStore = writable<Newsletter>();
export const newsletterRoleStore = writable<UserRole>();

export const newsletterStatsStore = writable<NewsletterStats>();
export const listStore = writable<List[]>([]);
export const subscriberMetadataDefinitionStore = writable<SubscriberMetadataDefinition[]>();
export const issueStore = writable<Issue[]>([]);
export const sendingProfileesStore = writable<SendingProfile[]>([]);

export function setNewsletterStore(newsletter: Newsletter) {
	newsletterStore.set(newsletter);
	newsletterEditingStore.set({ ...newsletter });
}

export function updateNewsletterStore(
	newsletter: Partial<Newsletter> | ((currentnewsletter: Newsletter) => Partial<Newsletter>)
) {
	const stores = [newsletterStore, newsletterEditingStore];

	stores.forEach((store) => {
		store.update((b) => {
			const val = typeof newsletter === 'function' ? newsletter(b) : newsletter;
			return { ...b, ...val };
		});
	});
}
