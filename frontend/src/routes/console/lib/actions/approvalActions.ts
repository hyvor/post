import consoleApi from "../consoleApi";
import type {Approval} from "../../types";

interface CreateApprovalParam {
    company_name: string;
    country: string;
    website: string;
    social_links: string | null;
    type_of_content: string | null;
    frequency: string | null;
    existing_list: string | null;
    sample: string | null;
    why_post: string | null;
}

export function createApproval(param: CreateApprovalParam) {

    const sanitizedParam = sanitizeParams(param)

    return consoleApi.post<Approval>({
        endpoint: 'approvals',
        data: sanitizedParam,
    });
}

interface UpdateApprovalParam {
    company_name: string | null;
    country: string | null;
    website: string | null;
    social_links: string | null;
    type_of_content: string | null;
    frequency: string | null;
    existing_list: string | null;
    sample: string | null;
    why_post: string | null;
}

export function updateApproval(id: number, param: UpdateApprovalParam) {

    const sanitizedParam = sanitizeParams(param)

    return consoleApi.post<Approval>({
        endpoint: `approvals/${id}`,
        data: sanitizedParam,
    });
}

function sanitizeParams(params: CreateApprovalParam | UpdateApprovalParam) {
    return Object.fromEntries(
        Object.entries(params).map(([key, value]) => [key, value === '' ? null : value])
    );
}
