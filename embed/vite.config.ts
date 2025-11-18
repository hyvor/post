import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';

// https://vite.dev/config/
export default defineConfig({
	plugins: [
		svelte({
			emitCss: false
		})
	],
	server: {
		port: 80,
		host: '0.0.0.0',
		allowedHosts: true
	},
	build: {
		emptyOutDir: false,
		rollupOptions: {
			input: {
				form: 'src/form/form.ts'
			},
			output: {
				entryFileNames: '[name].js',
				chunkFileNames: '[name].js'
			}
		}
	}
});
