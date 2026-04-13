let loaded = false;

export async function importCodemirrorAll() {
	if (loaded) return;

	const CodeMirror = await import('codemirror');

	(window as any).CodeMirror = CodeMirror.default;

	// @ts-ignore
	await import('codemirror/addon/display/autorefresh');
	// @ts-ignore
	await import('codemirror/addon/comment/comment');
	// @ts-ignore
	await import('codemirror/addon/edit/matchbrackets');
	// @ts-ignore
	await import('codemirror/addon/edit/matchtags');
	// @ts-ignore
	await import('codemirror/addon/edit/closebrackets');
	// @ts-ignore
	await import('codemirror/addon/edit/closetag');
	// @ts-ignore
	await import('codemirror/keymap/sublime');
	await import('codemirror/lib/codemirror.css');
	await import('codemirror/theme/solarized.css');

	// default languages

	// @ts-ignore
	await import('codemirror/mode/javascript/javascript'); // js
	// @ts-ignore
	await import('codemirror/mode/twig/twig'); // twig
	// @ts-ignore
	await import('codemirror/mode/htmlmixed/htmlmixed'); // html
	// @ts-ignore
	await import('codemirror/mode/css/css'); // css|scss

	loaded = true;
}

export const CODEMIRROR_MODES = {
	scss: { name: 'twig', base: 'text/x-scss' },
	twig: { name: 'twig', base: 'text/html' },
	json: { name: 'javascript', base: 'application/json' },
	js: 'text/javascript',
	yaml: 'text/x-yaml'
};

export type CodeMirrorMode = keyof typeof CODEMIRROR_MODES;
