import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	plugins: [sveltekit()],
	server: {
		port: 80,
		host: '0.0.0.0',
		allowedHosts: true,
		watch: {
			ignored: ['**/node_modules/**', '**/.git/**', '**/dist/**', '**/build/**', '**/.svelte-kit/**']
		}
	}
});
