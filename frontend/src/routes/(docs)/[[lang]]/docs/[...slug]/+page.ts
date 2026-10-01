import { loadDocsPage } from '@hyvor/design/marketing';
import { getSections } from '$docs/docs/docs';

export async function load({ params, parent }) {
	const { lang } = await parent();

	return loadDocsPage({
		basepath: `${lang === 'en' ? '' : `/${lang}`}/docs`,
		rootName: 'Docs',
		sections: await getSections(lang),
		slug: params.slug ?? ''
	});
}
