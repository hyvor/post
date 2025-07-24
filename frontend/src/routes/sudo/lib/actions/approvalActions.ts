import sudoApi from "../sudoApi";
import type {Approval, ApprovalStatus} from "../../types";
import {ITEMS_PER_PAGE} from "../generalActions";

export function getApprovals(
    status: ApprovalStatus | null = null,
    limit: number = ITEMS_PER_PAGE,
    offset: number = 0
) {
    return sudoApi.get<Approval[]>({
        endpoint: 'approvals',
        data: {
            status,
            limit,
            offset
        }
    })
}

export function approve(
    id: number,
    status: ApprovalStatus,
) {
    return sudoApi.post<void>({
        endpoint: `approvals/${id}`,
        data: {
            id,
            status
        }
    })
}
