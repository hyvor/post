import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	plugins: [sveltekit()],
	resolve: {
		// ../docs (outside this directory) imports these
		dedupe: ['@hyvor/design', '@hyvor/icons']
	},
	server: {
		port: 80,
		host: '0.0.0.0',
		allowedHosts: true
	}
});
