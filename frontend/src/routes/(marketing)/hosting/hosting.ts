import Introduction from './Introduction.svelte';
import type { NavSectionConfig } from '@hyvor/design/marketing';

export const sections: NavSectionConfig[] = [
	{
		name: 'Hosting',
		navs: [
			{
				type: 'page',
				name: 'Get Started',
				slug: '',
				content: Introduction
			}
		]
	}
];
