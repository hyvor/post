import type { Component } from 'svelte';
import Introduction from './Introduction.svelte';

export const categories: Category[] = [
	{
		name: 'Hosting',
		pages: [
			{
				slug: '',
				name: 'Get Started',
				component: Introduction
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
