import consoleApi from "../consoleApi";

interface CreateApprovalParam {
    company_name: string;
    country: string;
    website: string;
    social_links?: string;
    type_of_content?: string;
    frequency?: string;
    existing_list?: string;
    sample?: string;
    why_post?: string;
}

export function createApproval(param: CreateApprovalParam) {

    const sanitizedParam = Object.fromEntries(
        Object.entries(param).map(([key, value]) => [key, value === '' ? null : value])
    );

    return consoleApi.post<void>({
        endpoint: 'approvals',
        data: sanitizedParam,
    });
}
