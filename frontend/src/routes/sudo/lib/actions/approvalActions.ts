import sudoApi from "../sudoApi";
import type {Approval, ApprovalStatus} from "../../types";
import {ITEMS_PER_PAGE} from "../generalActions";

export const APPROVAL_STATUS_FILTERS = {
    reviewing: 'Reviewing',
    approved: 'Approved',
    rejected: 'Rejected',
};

export const IMPORT_STATUS_FILTERS = {
    requires_input: 'Requires Input',
    pending_approval: 'Pending Approval',
    importing: 'Importing',
    failed: 'Failed',
    completed: 'Completed',
}

export function getApprovals(
    user_id: number | null = null,
    status: ApprovalStatus | null = null,
    limit: number = ITEMS_PER_PAGE,
    offset: number = 0
) {
    return sudoApi.get<Approval[]>({
        endpoint: 'approvals',
        data: {
            user_id,
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
