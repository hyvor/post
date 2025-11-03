import {writable} from 'svelte/store';
import {
    type NewsletterStats,
    type Newsletter,
    type List,
    type Issue,
    type UserRole,
    type SubscriberMetadataDefinition,
    type SendingProfile,
    type Import,
    type NewsletterPermissions,
    type NewsletterList
} from '../../types';

export const newsletterStore = writable<Newsletter>();
export const newsletterLicenseStore = writable<boolean>(false);
export const newsletterEditingStore = writable<Newsletter>();
export const newsletterRoleStore = writable<UserRole>();
export const newsletterPermissionsStore = writable<NewsletterPermissions>();
export const newsletterStatsStore = writable<NewsletterStats>();
export const listStore = writable<List[]>([]);
export const subscriberMetadataDefinitionStore = writable<SubscriberMetadataDefinition[]>();
export const issueStore = writable<Issue[]>([]);
export const sendingProfilesStore = writable<SendingProfile[]>([]);
export const importStore = writable<Import[]>([]);

export function setNewsletterStoreByNewsletterList(list: NewsletterList) {
    setNewsletterStore(list.newsletter);
    newsletterRoleStore.set(list.role);
}

export function setNewsletterStore(newsletter: Newsletter) {
    newsletterStore.set(newsletter);
    newsletterEditingStore.set({...newsletter});
}

export function updateNewsletterStore(
    newsletter: Partial<Newsletter> | ((currentnewsletter: Newsletter) => Partial<Newsletter>)
) {
    const stores = [newsletterStore, newsletterEditingStore];

    stores.forEach((store) => {
        store.update((b) => {
            const val = typeof newsletter === 'function' ? newsletter(b) : newsletter;
            return {...b, ...val};
        });
    });
}
