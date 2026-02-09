type CategoryName = 'CMS' | 'E-Commerce' | 'Services';

export interface Integration {
	name: string;
	subname?: string;
	category: CategoryName;
	logo: string;
	url: string;
	color?: string;
}

export const integrations: Integration[] = [
	{
		name: 'Ghost',
		category: 'CMS',
		logo: '/img/integrations/ghost.png',
		url: '/docs/install/ghost',
		color: '#67d0ff'
	},
	{
		name: 'Hyvor Blogs',
		category: 'CMS',
		logo: '/img/integrations/hyvor-blogs.png',
		url: '/docs/install/hyvorblogs',
		color: '#ce9896'
	},

	{
		name: 'One.com',
		category: 'CMS',
		logo: '/img/integrations/one.webp',
		url: '/docs/install/onecom',
		color: '#1e88e5'
	},
	{
		name: 'Wix',
		category: 'CMS',
		logo: '/img/integrations/wix.png',
		url: '/docs/install/wix',
		color: '#f5a524'
	},
	{
		name: 'Squarespace',
		category: 'CMS',
		logo: '/img/integrations/squarespace.png',
		url: '/docs/install/squarespace',
		color: '#000000'
	},
	{
		name: 'Webflow',
		category: 'CMS',
		logo: '/img/integrations/webflow.png',
		url: '/docs/install/webflow',
		color: '#4299e1'
	},
	{
		name: 'Framer',
		category: 'CMS',
		logo: '/img/integrations/framer.png',
		url: '/docs/install/framer',
		color: '#0055ff'
	}

	// {
	//     name: 'Google Forms',
	//     category: 'Services',
	//     logo: "/img/integrations/google-forms.png",
	//     url: '/docs/install/google-forms',
	//     color: '#34a853'
	// },
	// {
	//     name: 'Typeform',
	//     category: 'Services',
	//     logo: "/img/integrations/typeform.png",
	//     url: '/docs/install/typeform',
	//     color: '#000000'
	// },
	// {
	//     name: 'Shopify',
	//     category: 'E-Commerce',
	//     logo: "/img/integrations/shopify.png",
	//     url: '/docs/install/shopify',
	//     color: '#96bf48'
	// }
];

interface category {
	name: CategoryName;
	title: string;
	integrations: Integration[];
}

export const categories: category[] = [
	{
		name: 'CMS',
		title: 'Content Management Systems',
		integrations: integrations.filter((integration) => integration.category === 'CMS')
	},
	{
		name: 'E-Commerce',
		title: 'E-Commerce Platforms',
		integrations: integrations.filter((integration) => integration.category === 'E-Commerce')
	},
	{
		name: 'Services',
		title: 'Services',
		integrations: integrations.filter((integration) => integration.category === 'Services')
	}
];
