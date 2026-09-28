import { loadDocsPage } from '@hyvor/design/marketing';
import { getSections } from '$docs/hosting/hosting';

export async function load({ params, parent }) {
	const { lang } = await parent();

	return loadDocsPage({
		basepath: `${lang === 'en' ? '' : `/${lang}`}/hosting`,
		rootName: 'Hosting',
		sections: await getSections(lang),
		slug: params.slug ?? ''
	});
}
