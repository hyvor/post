export interface AppConfig {
    template_defaults: {
		LANG: string,
		COLOR_ACCENT: string,
		COLOR_BACKGROUND: string,
		COLOR_BOX_BACKGROUND: string,
		BOX_RADIUS: string,
		BOX_SHADOW: string,
		BOX_BORDER: string,
		FONT_FAMILY: string,
		FONT_SIZE: string,
		FONT_WEIGHT: string,
		FONT_WEIGHT_HEADING: string,
		FONT_COLOR_ON_BACKGROUND: string,
		FONT_COLOR_ON_BOX: string,
		FONT_LINE_HEIGHT: string,
	}
}

export type ProjectMeta = {
	template_color_accent: string | null,
	template_color_background: string | null,
	template_color_box_background: string | null,
	template_box_shadow: string | null,
	template_box_border: string | null,
	template_font_family: string | null,
	template_font_size: string | null,
	template_font_weight: string | null,
	template_font_weight_heading: string | null,
	template_font_color_on_background: string | null,
	template_font_color_on_box: string | null,
	template_font_line_height: string | null,
	template_box_radius: string | null,
}

export type UserMini = {
	name: string,
	username: string | null,
	picture: string | null,
}

export type UserRole = 'owner' | 'admin';

export type User = {
	role: UserRole,
	created_at: number,
	user: UserMini,
}

export type ProjectList = {
	id: number,
	created_at: number,
	name: string,
	role: UserRole,
} & ProjectMeta;

export type Project = {
	id: number,
	created_at: number,
	name: string,
} & ProjectMeta;

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

export type Domain = {
	id: number,
	domain: string,
	dkim_public_key: string,
	dkim_txt_name: string,
	dkim_txt_value: string,
	verified: boolean,
	verified_in_ses: boolean,
	requested_by_current_website: boolean,
}

type VerifyDomainResponse = {
    domain: Domain;
    data: {
        verified: boolean;
        debug: Record<string, string>;
    };
};
