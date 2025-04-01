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
	description: string | null,
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
	uuid: string,
	created_at: number,
	subject: string,
	from_name: string,
	from_email: string,
	reply_to_email: string,
	content: string,
	status: IssueStatus,
	lists: number[],
	scheduled_at: number | null,
	sending_at: number | null,
	sent_at: number | null,
}

export type SendStatus = 'pending' | 'sent' | 'failed';
export type SendType = 'all' | 'opened' | 'clicked' | 'unsubscribed' | 'bounced' | 'complained';

export interface IssueSend {
	id: number;
	created_at: number;
	subscriber: Subscriber | null;
	email: string;
	status: SendStatus;
	sent_at: number | null;
	failed_at: number | null;
	delivered_at: number | null;
	first_opened_at: number | null;
	last_opened_at: number | null;
	first_clicked_at: number | null;
	last_clicked_at: number | null;
	unsubscribed_at: number | null;
	bounced_at: number | null;
	hard_bounce: boolean;
	complained_at: number | null;
	open_count: number;
	click_count: number;
}
