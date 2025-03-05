// TODO: remove the OR operator after completing the docs

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
		name: 'Hosting',
		pages: [
			{
				slug: 'custom-domain',
				name: 'Custom  Domain'
				// component: CustomDomain
			},
			{
				slug: 'subdirectory',
				name: 'Subdirectory'
				// component: SubDirectoryHosting
			}
		]
	},

	{
		name: 'Features',
		pages: [
			{
				slug: 'languages',
				name: 'Languages'
				// component: add component name
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
