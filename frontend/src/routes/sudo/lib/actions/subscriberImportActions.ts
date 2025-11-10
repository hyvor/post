import {ITEMS_PER_PAGE} from "../generalActions";
import sudoApi from "../sudoApi";
import type {ImportingSubscriber, SubscriberImport, SubscriberImportStatus} from "../../types";

export function getSubscriberImports(
    subdomain: string | null = null,
    status: SubscriberImportStatus | null = null,
    limit: number = ITEMS_PER_PAGE,
    offset: number = 0
) {
    return sudoApi.get<SubscriberImport[]>({
        endpoint: 'subscriber-imports',
        data: {
            subdomain,
            status,
            limit,
            offset
        }
    });
}

export function getImportingSubscribers(
    subscriberImportId: number,
    limit: number = ITEMS_PER_PAGE,
    offset: number = 0
) {
    return sudoApi.get<ImportingSubscriber[]>({
        endpoint: 'subscriber-imports/' + subscriberImportId,
        data: {
            limit,
            offset
        }
    });
}

export function approveSubscriptionImport(subscriberImportId: number) {
    return sudoApi.post<SubscriberImport>({
        endpoint: 'subscriber-imports/' + subscriberImportId
    });
}