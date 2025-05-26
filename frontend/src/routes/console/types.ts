export interface AppConfig {

	hyvor: {
		instance: string;
	};

	app: {
		default_email_domain: string;
	};

	newsletter_defaults: {
		FORM_COLOR_LIGHT_TEXT: string;
		FORM_COLOR_LIGHT_ACCENT: string;
		FORM_COLOR_LIGHT_ACCENT_TEXT: string;
		FORM_COLOR_LIGHT_INPUT: string;
		FORM_COLOR_LIGHT_INPUT_TEXT: string;
		FORM_LIGHT_INPUT_BOX_SHADOW: string;
		FORM_LIGHT_INPUT_BORDER: string;
		FORM_LIGHT_BORDER_RADIUS: string;

		TEMPLATE_LANG: string;
		TEMPLATE_COLOR_ACCENT: string;
		TEMPLATE_COLOR_ACCENT_TEXT: string;
		TEMPLATE_COLOR_BACKGROUND: string;
		TEMPLATE_COLOR_BACKGROUND_TEXT: string;
		TEMPLATE_COLOR_BOX: string;
		TEMPLATE_COLOR_BOX_TEXT: string;
		TEMPLATE_BOX_RADIUS: string;
		TEMPLATE_BOX_SHADOW: string;
		TEMPLATE_BOX_BORDER: string;
		TEMPLATE_FONT_FAMILY: string;
		TEMPLATE_FONT_SIZE: string;
		TEMPLATE_FONT_WEIGHT: string;
		TEMPLATE_FONT_WEIGHT_HEADING: string;
		TEMPLATE_FONT_COLOR_ON_BACKGROUND: string;
		TEMPLATE_FONT_COLOR_ON_BOX: string;
		TEMPLATE_FONT_LINE_HEIGHT: string;
	};
}

export type NewsletterMeta = {
	template_color_accent: string | null;
	template_color_accent_text: string | null;
	template_color_background: string | null;
	template_color_background_text: string | null;
	template_color_box: string | null;
	template_color_box_text: string | null;
	template_box_shadow: string | null;
	template_box_border: string | null;
	template_font_family: string | null;
	template_font_size: string | null;
	template_font_weight: string | null;
	template_font_weight_heading: string | null;
	template_font_line_height: string | null;
	template_box_radius: string | null;

	form_width: number | null;
	form_custom_css: string | null;

	form_title: string | null;
	form_description: string | null;
	form_footer_text: string | null;
	form_button_text: string | null;
	form_success_message: string | null;

	form_color_light_text: string | null;
	form_color_light_text_light: string | null;
	form_color_light_accent: string | null;
	form_color_light_accent_text: string | null;
	form_color_light_input: string | null;
	form_color_light_input_text: string | null;
	form_light_input_box_shadow: string | null;
	form_light_input_border: string | null;
	form_light_border_radius: string | null;

	form_color_dark_text: string | null;
	form_color_dark_text_light: string | null;
	form_color_dark_accent: string | null;
	form_color_dark_accent_text: string | null;
	form_color_dark_input: string | null;
	form_color_dark_input_text: string | null;
	form_dark_input_box_shadow: string | null;
	form_dark_input_border: string | null;
	form_dark_border_radius: string | null;
};

export type UserMini = {
	name: string;
	username: string | null;
	picture_url: string | null;
};

export type UserRole = 'owner' | 'admin';

export type User = {
	id: number;
	role: UserRole;
	created_at: number;
	user: UserMini;
};

export type Invite = {
	id: number;
	created_at: number;
	role: UserRole;
	user: UserMini;
	expires_at: number;
};

export type NewsletterList = {
	role: UserRole;
	newsletter: Newsletter;
};

export type Newsletter = {
	id: number,
	slug: string,
	uuid: string,
	created_at: number,
	name: string,
} & NewsletterMeta;

export interface SubscriberMetadataDefinition {
	id: number;
	created_at: number;
	key: string;
	name: string;
	type: 'text';
}

interface StatsType {
	total: number;
	last_30_days: number;
}

export interface NewsletterStats {
	subscribers: StatsType;
	issues: StatsType;
	open_rate: StatsType;
	click_rate: StatsType;
}

export type List = {
	id: number;
	created_at: number;
	name: string;
	description: string | null;
	subscribers_count: number;
	subscribers_count_last_30d: number;
};

export type NewsletterSubscriberStatus = 'subscribed' | 'unsubscribed' | 'pending';
export type NewsletterSubscriberSource = 'console' | 'form' | 'import';

export type Subscriber = {
	id: number;
	email: string;
	status: NewsletterSubscriberStatus;
	list_ids: number[];
	source: NewsletterSubscriberSource;
	subscribed_at: number;
	unsubscribed_at: number | null;
};

export type IssueStatus = 'draft' | 'scheduled' | 'sending' | 'failed' | 'sent';

export type Issue = {
	id: number;
	uuid: string;
	created_at: number;
	subject: string;
	from_name: string;
	from_email: string;
	reply_to_email: string;
	content: string;
	status: IssueStatus;
	lists: number[];
	scheduled_at: number | null;
	sending_at: number | null;
	sent_at: number | null;
};

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
	id: number;
	domain: string;
	dkim_public_key: string;
	dkim_txt_name: string;
	dkim_txt_value: string;
	verified: boolean;
	verified_in_ses: boolean;
	requested_by_current_website: boolean;
};

export type SendingAddress = {
	id: number,
	email: string,
	domain: Domain,
	is_default: boolean,
}
