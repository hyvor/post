import type { Schema } from 'prosemirror-model';
import { buildInputRules, buildKeymap } from 'prosemirror-example-setup';
import { keymap } from 'prosemirror-keymap';
import { history } from 'prosemirror-history';
import { baseKeymap } from 'prosemirror-commands';

export function getPlugins(schema: Schema) {
	const plugins = [
		buildInputRules(schema),

		keymap(buildKeymap(schema)),
		keymap(baseKeymap),

		history()
	];

	return plugins;
}
