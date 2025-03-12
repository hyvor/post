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
}

export type NewsletterSubscriberStatus = 'subscribed' | 'unsubscribed' | 'pending';
export type NewsletterSubscriberSource = 'manual' | 'api' | 'import';

export type Subscriber ={
	id: number,
	email: string,
	status: NewsletterSubscriberStatus,
	list_ids: number[],
	source: NewsletterSubscriberSource,
	subscribed_at: number,
	unsubscribed_at: number | null,
}