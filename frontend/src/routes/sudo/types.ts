export type ApprovalStatus = 'pending' | 'reviewing' | 'approved' | 'rejected';

export type Approval = {
    id: number;
    created_at: number;
    user_id: number;
    status: ApprovalStatus;
    company_name: string;
    country: string;
    website: string;
    social_links: string | null;
    type_of_content: string | null;
    frequency: string | null;
    existing_list: string | null;
    sample: string | null;
    why_post: string | null;
    public_note: string | null;
    private_note: string | null;
    approved_at: number | null;
    rejected_at: number | null;
}
