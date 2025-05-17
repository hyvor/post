import { MARKETING_LANGUAGES } from './locale.js';

export const prerender = true;

export function load({ params }) {
	const { lang } = params;
	const marketingLanguageCodes = MARKETING_LANGUAGES.map((l) => l.code);

	if (marketingLanguageCodes.includes(lang) === false) {
		throw new Error(`Unsupported language: ${lang}`);
	}

	return { lang };
}
