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
	limit: number,
	offset: number
) {
	return consoleApi.get<Subscriber[]>({
		endpoint: 'subscribers',
		data: {
			status,
			limit,
			offset
		}
	});
}