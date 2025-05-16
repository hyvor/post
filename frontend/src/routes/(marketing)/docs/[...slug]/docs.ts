import EmailDesign from './content/EmailDesign.svelte';
import Introduction from './content/Introduction.svelte';
import type { Component } from 'svelte';
import SignupForm from './content/SignupForm.svelte';

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
				slug: 'design',
				name: 'Email Design',
				component: EmailDesign
			},
			{
				slug: 'form',
				name: 'Signup Form',
				component: SignupForm
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
