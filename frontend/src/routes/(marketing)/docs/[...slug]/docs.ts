import EmailDesign from "./content/EmailDesign.svelte";
import Introduction from "./content/Introduction.svelte";
import type { Component } from "svelte";
import SignupForm from "./content/SignupForm.svelte";
import Import from "./content/Import/Import.svelte";
import Approval from "./content/Approval.svelte";
import ConsoleApi from "./content/ConsoleApi.svelte";

export const categories: Category[] = [
  {
    name: "Intro",
    pages: [
      {
        slug: "",
        name: "Introduction",
        component: Introduction,
      },
      {
        slug: "approval",
        name: "Approval",
        component: Approval,
      },
    ],
  },

  {
    name: "Features",
    pages: [
      {
        slug: "form",
        name: "Signup Form",
        component: SignupForm,
      },
      {
        slug: "design",
        name: "Email Design",
        component: EmailDesign,
      },
      {
        slug: "import",
        name: "Import",
        component: Import,
      },
    ],
  },
  {
    name: "Developer",
    pages: [
      // {
      // 	slug: 'webhooks',
      // 	name: 'Webhooks'
      // 	// component: add component name
      // },
      {
        slug: "api-console",
        name: "Console API",
        component: ConsoleApi,
      },
    ],
  },
];

export const pages = categories.reduce(
  (acc, category) => acc.concat(category.pages),
  [] as Page[],
);

interface Category {
  name: string;
  pages: Page[];
}

interface Page {
  slug: string;
  name: string;
  component?: Component;
  parent?: string;
}
