// src/lib/locale.ts
import type { InternationalizationService } from "@hyvor/design/components";
import { getContext } from "svelte";
import en from "./locale/en.json";

type I18nType = InternationalizationService<typeof en>;

export function getMarketingI18n() {
  return getContext<I18nType>("i18n");
}

export const MARKETING_LANGUAGES = [
  {
    code: "en",
    flag: "🇬🇧",
    name: "English",
    strings: en,
    default: true,
  },
];
