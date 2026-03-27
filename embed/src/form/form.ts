import { mount, unmount } from 'svelte';
import Form from './Form.svelte';
import formCss from './form.css?inline';

class HyvorPostForm extends HTMLElement {
	private form: Record<string, any> | null = null;

	constructor() {
		super();
		this.attachShadow({ mode: 'open' });

		const style = document.createElement('style');
		style.textContent = formCss;
		this.shadowRoot!.appendChild(style);

		window.addEventListener(
			'hb:colorModeChanged',
			(e: CustomEventInit<{ mode: 'light' | 'dark' }>) => {
				this.form?.setPalette(e.detail?.mode);
			}
		);
	}

	connectedCallback() {
		const newsletterSubdomain = this.getAttribute('newsletter');

		if (!newsletterSubdomain) {
			throw new Error('project-uuid is required for Hyvor Post form.');
		}

		const colors =
			this.getAttribute('colors') ||
			(document.documentElement.classList.contains('mode-dark') && 'dark') ||
			(document.documentElement.classList.contains('mode-light') && 'light') ||
			'os';

		this.form = mount(Form, {
			target: this.shadowRoot!,
			props: {
				newsletterSubdomain,
				instance: this.getAttribute('instance') || 'https://post.hyvor.com',
				shadowRoot: this.shadowRoot!,
				lists: this.getListsArr('lists'),
				listsDefaultUnselected: this.getListsArr('lists-default-unselected'),
				listsHidden: this.hasAttribute('lists-hidden'),
				colors: colors as 'light' | 'dark' | 'os'
			}
		});
	}

	disconnectedCallback() {
		if (this.form) {
			unmount(this.form);
			this.form = null;
		}
	}

	private getListsArr(attrName: string): string[] {
		const attr = this.getAttribute(attrName);
		if (!attr) {
			return [];
		}
		return attr.split(',').map((item) => item.trim());
	}

	static get observedAttributes() {
		return ['colors'];
	}

	attributeChangedCallback(name: string, oldVal: string, newVal: string) {
		if (name === 'colors' && oldVal !== newVal) {
			this.form?.setPalette(newVal as any);
		}
	}
}

if (customElements.get('hyvor-post-form') === undefined) {
	customElements.define('hyvor-post-form', HyvorPostForm);
}
