import { type Component } from 'svelte';

import SquarespaceGuide from './guides/squarespace/SquarespaceGuide.svelte';
import WixGuide from './guides/wix/WixGuide.svelte';
import GhostGuide from './guides/ghost/GhostGuide.svelte';
import FramerGuide from './guides/framer/FramerGuide.svelte';
import OneGuide from './guides/one/OneGuide.svelte';
import HyvorBlogsGuide from './guides/hyvorblogs/HyvorBlogsGuide.svelte';
import HtmlGuide from './guides/html/HtmlGuide.svelte';

type PlatformsType = Record<
	string,
	{
		name: string;
		component?: Component;
		code?: true;
	}
>;

export const platforms: PlatformsType = {
	
	html: {
        name: 'HTML',
        component: HtmlGuide, // if you have one
        code: true
    },
	hyvorblogs: {
		name: 'Hyvor Blogs',
		component: HyvorBlogsGuide
	},

	squarespace: {
		name: 'Squarespace',
		component: SquarespaceGuide
	},

	wix: {
		name: 'Wix',
		component: WixGuide
	},

	ghost: {
		name: 'Ghost',
		component: GhostGuide
	},

	framer: {
		name: 'Framer',
		component: FramerGuide
	},

	one: {
		name: 'One.com',
		component: OneGuide
	}
};
