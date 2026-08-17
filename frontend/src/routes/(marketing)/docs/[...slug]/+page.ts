import { loadDocsPage } from '@hyvor/design/marketing';
import { sections } from './docs';

export async function load({ params }) {
	return loadDocsPage({
		basepath: '/docs',
		rootName: 'Docs',
		sections,
		slug: params.slug
	});
}
