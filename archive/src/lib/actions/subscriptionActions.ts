import publicApi from '$lib/publicApi.svelte';
import type { List } from '$lib/types';

const SUBSCRIBER_PREFIX = '/subscriber';

export function confirm(token: string) {
	return publicApi.get<void>({
		endpoint: SUBSCRIBER_PREFIX + '/confirm',
		data: { token }
	});
}

interface UnsubscribeResponse {
	lists: List[];
}

export function unsubscribe(token: string) {
	return publicApi.get<UnsubscribeResponse>({
		endpoint: SUBSCRIBER_PREFIX + '/unsubscribe',
		data: { token }
	});
}

export function resubscribe(list_ids: number[], token: string) {
	return publicApi.patch<void>({
		endpoint: SUBSCRIBER_PREFIX + '/resubscribe',
		data: {
			list_ids,
			token
		}
	});
}
