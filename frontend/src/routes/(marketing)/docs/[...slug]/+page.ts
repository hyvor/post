import { error } from '@sveltejs/kit';
import { pages } from './docs';
import { platforms } from '$lib/install/platforms';

export async function load({ params }) {
	const slug = params.slug;

	let page = null;
	let installPlatform = 'html';

	if (slug === undefined) {
		page = pages[0];
	} else if (slug.startsWith('install')) {
		page = pages.find((p) => p.slug === 'install');

		const platformName = slug.split('/')[1] || 'html';
		installPlatform = platformName;
		if (!platforms[platformName]) {
			page = null;
		}
	} else {
		page = pages.find((p) => p.slug === slug);
	}

	if (!page) {
		error(404, 'Not found');
	}

	return {
		slug: params.slug,
		name: page.name,
		component: page.component,
		installPlatform
	};
}
