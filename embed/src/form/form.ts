import { mount } from "svelte";
import Form from "./Form.svelte";

class HyvorPostForm extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: "open" });
    }

    connectedCallback() {
        const projectId = this.getAttribute("project-id");

        /* if (!websiteId) {
			throw new Error('website-id is required for Hyvor post Newsletter widget.');
		}

		const translations = Translations.fromElement(this); */

        mount(Form, {
            target: this.shadowRoot!,
        });

        /* this.component = new Newsletter({
			target: this.shadowRoot!,
			props: {
				websiteId: Number(websiteId),
				instance: this.getAttribute('instance'),
				title: this.getAttribute('title'),
				description: this.getAttribute('description'),
				language: this.getAttribute('language'),
				ssoUser: this.getAttribute('sso-user'),
				ssoHash: this.getAttribute('sso-hash'),
				colors: this.getAttribute('colors'),
				translations: translations as any,
				shadowRoot: this.shadowRoot!
			}
		}); */
    }

    /* static get observedAttributes() {
		return ['colors'];
	} */

    attributeChangedCallback(name: string, oldVal: string, newVal: string) {
        /* if (name === 'colors' && oldVal !== newVal) {
			styles.setStyles(newVal as any);
		} */
    }
}

if (customElements.get("hyvor-post-form") === undefined) {
    customElements.define("hyvor-post-form", HyvorPostForm);
}
