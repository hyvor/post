import publicApi from "$lib/publicApi";
import type {List} from "$lib/types";

const SUBSCRIBER_PREFIX = '/subscriber';

export function confirm(token: string) {
    return publicApi.get({
        endpoint: SUBSCRIBER_PREFIX + '/confirm',
        data: {token},
    });
}

interface UnsubscribeResponse {
    lists: List[]
}

export function unsubscribe(token: string) {
    return publicApi.get<UnsubscribeResponse>({
        endpoint: SUBSCRIBER_PREFIX + '/unsubscribe',
        data: {token},
    });
}
