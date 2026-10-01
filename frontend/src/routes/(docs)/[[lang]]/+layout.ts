import { error } from '@sveltejs/kit';
import { LANGUAGES } from '$docs/docs/docs';

const DEFAULT_LANGUAGE = 'en';

export async function load({ params }) {
	const lang = params.lang ?? DEFAULT_LANGUAGE;

	// the default language is served without a prefix (/docs, not /en/docs)
	if (params.lang === DEFAULT_LANGUAGE || !LANGUAGES.includes(lang)) {
		error(404, 'Not found');
	}

	return { lang };
}
