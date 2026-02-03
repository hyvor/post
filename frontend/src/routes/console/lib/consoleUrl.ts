import { get } from 'svelte/store';
import { newsletterStore } from './stores/newsletterStore';

export function consoleUrl(path: string) {
	path = path.replace(/^\//, '');

	return '/console/' + path;
}

export function consoleUrlWithNewsletter(path: string) {
	const newsletterSubdomain = get(newsletterStore).subdomain;
	path = path.replace(/^\//, '');
	return consoleUrl(`${newsletterSubdomain}/${path}`);
}
