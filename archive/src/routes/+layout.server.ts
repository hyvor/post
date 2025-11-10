import type { LayoutServerLoad } from './$types';
import { env } from '$env/dynamic/private';

export const load: LayoutServerLoad = async ({ request }) => {
	const host = request.headers.get('host') || '';
	const subdomain = host.split('.')[0];

	return {
		subdomain,
		config: {
			app_url: env.URL_APP
		}
	};
};
