import type { Node } from 'prosemirror-model';
import { EditorView, type NodeView } from 'prosemirror-view';

export default class HeadingNodeView implements NodeView {
	dom: HTMLElement;
	contentDOM: HTMLElement;

	constructor(node: Node, view: EditorView, getPos: () => number | undefined) {
		this.dom = document.createElement('div');
		this.dom.classList.add('heading-wrap');

		const headingSelectorsWrap = document.createElement('div');
		headingSelectorsWrap.classList.add('heading-selectors-wrap');
		[1, 2, 3, 4, 5, 6].map((level) => {
			const selector = document.createElement('button');
			selector.type = 'button';
			selector.classList.add('heading-selector');
			selector.textContent = 'H' + level;
			selector.addEventListener('click', () => {
				const { state, dispatch } = view;
				const { tr } = state;
				tr.setNodeMarkup(getPos()!, null, { level });
				dispatch(tr);
			});

			if (node.attrs.level === level) {
				selector.classList.add('selected');
			}

			headingSelectorsWrap.appendChild(selector);
			return selector;
		});
		this.dom.appendChild(headingSelectorsWrap);

		this.contentDOM = document.createElement('h' + node.attrs.level);
		this.dom.appendChild(this.contentDOM);
	}
}
