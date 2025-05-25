import type { List, Newsletter, NewsletterStats, SendingAddress, SubscriberMetadataDefinition } from '../types';
import consoleApi from './consoleApi';
import {
	issueStore,
	listStore,
	newsletterStatsStore,
	sendingAddressesStore,
	setNewsletterStore,
	subscriberMetadataDefinitionStore
} from './stores/newsletterStore';

interface NewsletterResponse {
	newsletter: Newsletter;
	stats: NewsletterStats;
	lists: List[];
	subscriber_metadata_definitions: SubscriberMetadataDefinition[];
	sending_addresses: SendingAddress[];
}

// to prevent multiple requests for the same subdomain
const LOADER_PROMISES: Record<string, Promise<NewsletterResponse>> = {};

export function loadNewsletter(newsletterId: string) {
	if (LOADER_PROMISES[newsletterId] !== undefined) {
		return LOADER_PROMISES[newsletterId];
	}

	const promise = new Promise<NewsletterResponse>((resolve, reject) => {
		consoleApi
			.get<NewsletterResponse>({
				endpoint: 'init/newsletter',
				userApi: true,
				newsletterId: newsletterId
			})
			.then((res) => {
				setNewsletterStore(res.newsletter);
				newsletterStatsStore.set(res.stats);
				listStore.set(res.lists);
				subscriberMetadataDefinitionStore.set(res.subscriber_metadata_definitions);
				sendingAddressesStore.set(res.sending_addresses);

				issueStore.set([]);

				resolve(res);
			})
			.catch((err) => {
				reject(err);
			})
			.finally(() => {
				delete LOADER_PROMISES[newsletterId];
			});
	});

	LOADER_PROMISES[newsletterId] = promise;

	return promise;
}
