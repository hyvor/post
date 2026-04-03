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

export type Newsletter = {
	id: number;
	created_at: number;
	subdomain: string;
	name: string;
	user_id: number;
	organization_id: number | null;
	language_code: string | null;
	is_rtl: boolean;
};

export type IssueStatus = 'draft' | 'scheduled' | 'sending' | 'sent';

export type Issue = {
	id: number;
	created_at: number;
	uuid: string;
	subject: string | null;
	status: IssueStatus;
	newsletter_subdomain: string;
	newsletter_id: number;
	scheduled_at: number | null;
	sending_at: number | null;
	sent_at: number | null;
	total_sendable: number;
	error_private: string | null;
};

export type NewsletterStats = {
	subscribers: { total: number; last_30_days: number };
	issues: { total: number; last_30_days: number };
	bounced_rate: { total: number; last_30_days: number };
	complained_rate: { total: number; last_30_days: number };
	lists_count: number;
	sending_profiles_count: number;
};
