export type Project = {
	id: number,
	created_at: number,
	name: string,
}

export interface ProjectStats {
	subscribers: { total: number; last_30d: number };
	issues: { total: number; last_30d: number };
	lists: { total: number; last_30d: number };
}

export type List = {
	id: number,
	created_at: number,
	name: string,
	subscribers_count: number,
	subscribers_count_last_30d: number,
}

export type NewsletterSubscriberStatus = 'subscribed' | 'unsubscribed' | 'pending';
export type NewsletterSubscriberSource = 'manual' | 'api' | 'import';

export type Subscriber = {
	id: number,
	email: string,
	status: NewsletterSubscriberStatus,
	list_ids: number[],
	source: NewsletterSubscriberSource,
	subscribed_at: number,
	unsubscribed_at: number | null,
}

export type IssueStatus = 'draft' | 'scheduled' | 'sending' | 'failed' | 'sent';

export type Issue = {
	id: number,
	created_at: number,
	subject: string | null,
	from_name: string | null,
	from_email: string,
	reply_to_email: string | null,
	content: string | null,
	status: IssueStatus,
	lists: number[],
	scheduled_at: number | null,
	sending_at: number | null,
	sent_at: number | null,
}
