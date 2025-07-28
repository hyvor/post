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
    approval: Approval,
    status: ApprovalStatus,
) {
    return sudoApi.post<Approval>({
        endpoint: `approvals/${approval.id}`,
        data: {
            id: approval.id,
            status: status,
            public_note: approval.public_note || null,
            private_note: approval.private_note || null
        }
    })
}
