import type { NewsletterSubscriberStatus, Subscriber } from "../../types";
import consoleApi from "../consoleApi";

export function createSubscriber(email: string, list_ids: number[]) {
    return consoleApi.post<Subscriber>({
        endpoint: 'subscribers',
        data: {
            email,
            list_ids,
        },
    });
}

export function getSubscribers(
	status: NewsletterSubscriberStatus,
	list_id: number | null,
	search : string | null,
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

export function updateSubscriber(id: number, data: Partial<Subscriber>) {
	return consoleApi.patch<Subscriber>({
		endpoint: `subscribers/${id}`,
		data
	});
}