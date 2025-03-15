import EmailTemplates from './content/EmailTemplates.svelte';
import Introduction from './content/Introduction.svelte';
import type { Component } from 'svelte';

export const categories: Category[] = [
	{
		name: 'Intro',
		pages: [
			{
				slug: '',
				name: 'Introduction',
				component: Introduction
			}
		]
	},

	{
		name: 'Features',
		pages: [
			{
				slug: 'email-templates',
				name: 'Email Templates',
				component: EmailTemplates
			}
		]
	},

	{
		name: 'Developer',
		pages: [
			{
				slug: 'webhooks',
				name: 'Webhooks'
				// component: add component name
			},
			{
				slug: 'api-console',
				name: 'Console API'
				// component: add component name
			}
		]
	}
];

export const pages = categories.reduce((acc, category) => acc.concat(category.pages), [] as Page[]);

interface Category {
	name: string;
	pages: Page[];
}

interface Page {
	slug: string;
	name: string;
	component?: Component;
	parent?: string;
}
