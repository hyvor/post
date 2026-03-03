import { ITEMS_PER_PAGE } from '../generalActions';
import sudoApi from '../sudoApi';
import type { NewsletterStats, Newsletter } from '../../types';

export function getNewsletters(
	name: string | null = null,
	limit: number = ITEMS_PER_PAGE,
	offset: number = 0
) {
	return sudoApi.get<Newsletter[]>({
		endpoint: 'newsletters',
		data: {
			name,
			limit,
			offset
		}
	});
}

export function getNewsletter(id: number) {
	return sudoApi.get<{ newsletter: Newsletter; stats: NewsletterStats }>({
		endpoint: `newsletters/${id}`
	});
}
