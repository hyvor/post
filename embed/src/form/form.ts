import { mount } from "svelte";
import Form from "./Form.svelte";
import formCss from './form.css?inline'

class HyvorPostForm extends HTMLElement {

    private form: Record<string, any> | null = null;

    constructor() {
        super();
        this.attachShadow({ mode: "open" });
    }

    connectedCallback() {
        const projectUuid = this.getAttribute("project");

        if (!projectUuid) {
            throw new Error('project-uuid is required for Hyvor Post form.');
        }

        this.form = mount(Form, {
            target: this.shadowRoot!,
            props: {
                projectUuid,
                instance: this.getAttribute("instance") || "https://post.hyvor.com",
                shadowRoot: this.shadowRoot!,
            }
        });

        const style = document.createElement("style");
        style.textContent = formCss;
        this.shadowRoot!.appendChild(style);
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

if (customElements.get("hyvor-post-form") === undefined) {
    customElements.define("hyvor-post-form", HyvorPostForm);
}
