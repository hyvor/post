export interface SudoConfig {
	hyvor: {
		instance: string;
	};
}

export interface SudoStats {
	reviewing_approvals: number;
	pending_imports: number;
}

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
};

export type SubscriberImportStatus =
	| 'requires_input'
	| 'pending_approval'
	| 'importing'
	| 'failed'
	| 'completed';

export type SubscriberImport = {
	id: number;
	created_at: number;
	newsletter_subdomain: string;
	status: SubscriberImportStatus;
	total_rows: number;
	source: string;
	columns: string[];
};

export type ImportingSubscriber = {
	email: string;
	lists: string[];
	status: 'pending' | 'subscribed' | 'unsubscribed';
	subscribed_at: number | null;
	subscribe_ip: string | null;
	metadata: Record<string, string> | null;
};
