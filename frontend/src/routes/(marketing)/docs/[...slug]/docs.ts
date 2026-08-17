import EmailDesign from './content/EmailDesign.svelte';
import Introduction from './content/Introduction.svelte';
import SignupForm from './content/SignupForm.svelte';
import Import from './content/Import/Import.svelte';
import ConsoleApi from './content/ConsoleApi.svelte';
import type { NavSectionConfig } from '@hyvor/design/marketing';

export const sections: NavSectionConfig[] = [
	{
		navs: [
			{
				type: 'page',
				name: 'Introduction',
				slug: '',
				content: Introduction
			}
		]
	},
	{
		name: 'Features',
		navs: [
			{
				type: 'page',
				name: 'Signup Form',
				slug: 'form',
				content: SignupForm
			},
			{
				type: 'page',
				name: 'Email Design',
				slug: 'design',
				content: EmailDesign
			},
			{
				type: 'page',
				name: 'Import',
				slug: 'import',
				content: Import
			}
		]
	},
	{
		name: 'Developer',
		navs: [
			{
				type: 'page',
				name: 'Console API',
				slug: 'api-console',
				content: ConsoleApi
			}
		]
	}
];
