/**
 * Navigation of the Hyvor Post self-hosting docs (hyvor.com/post/hosting).
 *
 * This directory is synced to hyvor/core, which renders the docs.
 * Keep it self-contained: only import from within this directory,
 * `svelte`, and `@hyvor/design`.
 */
import type { NavSectionConfig } from "@hyvor/design/marketing";
import type { Component } from "svelte";
import en from "./locale/en.json";
import fr from "./locale/fr.json";

export const LANGUAGES = ["en", "fr"];

const STRINGS: Record<string, typeof en> = { en, fr };

const PAGES = import.meta.glob<{ default: Component }>("./*/*.md");

// loads a page in the given language, falling back to English if it is not translated yet
async function loadPage(lang: string, file: string): Promise<Component> {
    const loader = PAGES[`./${lang}/${file}.md`] ?? PAGES[`./en/${file}.md`];
    if (!loader) {
        throw new Error(`Page not found: ${file}`);
    }
    return (await loader()).default;
}

export async function getSections(lang: string): Promise<NavSectionConfig[]> {
    const s = STRINGS[lang] ?? en;
    const getComponent = (file: string) => loadPage(lang, file);

    return [
        {
            name: s.sections.hosting,
            navs: [
                {
                    type: "page",
                    slug: "",
                    name: s.pages.getStarted,
                    content: await getComponent("Introduction"),
                },
            ],
        },
    ];
}
