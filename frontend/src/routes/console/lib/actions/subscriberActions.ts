import type { NewsletterSubscriberStatus, Subscriber } from '../../types';
import consoleApi from '../consoleApi';

export interface CreateSubscriberParams {
	lists?: number[];
	status?: 'pending' | 'subscribed';
	list_skip_resubscribe_on?: string[];
	lists_strategy?: 'merge' | 'overwrite' | 'remove';
	list_removal_reason?: 'unsubscribe' | 'bounce' | 'other';
	metadata?: Record<string, any>;
	metadata_strategy?: 'merge' | 'overwrite';
}

export function createSubscriber(email: string, params: CreateSubscriberParams) {
	return consoleApi.post<Subscriber>({
		endpoint: 'subscribers',
		data: {
			email,
			...params
		}
	});
}

export function getSubscribers(
	status: NewsletterSubscriberStatus | null,
	list_id: number | null,
	search: string | null,
	limit: number,
	offset: number
) {
	return consoleApi.get<Subscriber[]>({
		endpoint: 'subscribers',
		data: {
			status,
			list_id,
			search,
			limit,
			offset
		}
	});
}

export function deleteSubscriber(id: number) {
	return consoleApi.delete<Subscriber>({
		endpoint: `subscribers/${id}`
	});
}

interface BulkSubscriberActionResponse {
	status: string;
	message: string;
	subscribers: Subscriber[];
}

export function deleteSubscribers(ids: number[]) {
	return consoleApi.post<BulkSubscriberActionResponse>({
		endpoint: 'subscribers/bulk',
		data: {
			action: 'delete',
			subscribers_ids: ids
		}
	});
}

export function updateSubscribersMetadata(ids: number[], metadata: Record<string, string>) {
	return consoleApi.post<BulkSubscriberActionResponse>({
		endpoint: 'subscribers/bulk',
		data: {
			action: 'metadata_update',
			subscribers_ids: ids,
			metadata
		}
	});
}
