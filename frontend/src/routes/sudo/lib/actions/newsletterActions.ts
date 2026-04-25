import { ITEMS_PER_PAGE } from '../generalActions';
import sudoApi from '../sudoApi';
import type { NewsletterStats, Newsletter } from '../../types';

export interface Organization {
	id: number;
	name: string;
	billing_email: string | null;
	billing_address: {
		country: string | null;
	} | null;
}

export function getNewsletters(
	name: string | null = null,
	organizationId: number | null = null,
	limit: number = ITEMS_PER_PAGE,
	offset: number = 0,
	sort: string = 'id_desc'
) {
	return sudoApi.get<{ newsletters: Newsletter[]; orgs: Organization[] }>({
		endpoint: 'newsletters',
		data: {
			name,
			organization_id: organizationId,
			limit,
			offset,
			sort
		}
	});
}

export function getNewsletter(id: number) {
	return sudoApi.get<{ newsletter: Newsletter; stats: NewsletterStats }>({
		endpoint: `newsletters/${id}`
	});
}

export interface NewsletterRowStats {
	issues_count: number;
	subscribers_count: number;
}

export function getNewslettersBatchStats(ids: number[]) {
	return sudoApi.get<{ stats: Record<number, NewsletterRowStats> }>({
		endpoint: 'newsletters/stats',
		data: { ids }
	});
}
