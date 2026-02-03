import consoleApi from '../consoleApi';
import type { Newsletter } from '../../types';
import { updateNewsletterStore } from '../stores/newsletterStore';

export function getSubdomainAvailability(subdomain: string) {
	return consoleApi.post<{ available: boolean }>({
		endpoint: 'newsletter/subdomain',
		data: { subdomain },
		userApi: true
	});
}

export function createNewsletter(name: string, subdomain: string) {
	return consoleApi.post<Newsletter>({
		endpoint: 'newsletter',
		userApi: true,
		data: {
			name,
			subdomain
		}
	});
}

export async function updateNewsletter(
	newsletter: Partial<Omit<Newsletter, 'id' | 'created_at'>>,
	updateStore = false
) {
	const res = await consoleApi.patch<Newsletter>({
		endpoint: 'newsletter',
		data: newsletter
	});

	if (updateStore) {
		updateNewsletterStore(res);
	}

	return res;
}

export function deleteNewsletter() {
	return consoleApi.delete<Newsletter>({
		endpoint: 'newsletter'
	});
}
