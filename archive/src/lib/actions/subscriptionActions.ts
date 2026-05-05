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

export function changePreferences(token: string, list_ids: number[]) {
	return publicApi.post<UnsubscribeResponse>({
		endpoint: SUBSCRIBER_PREFIX + '/preferences',
		data: { token, list_ids }
	});
}
