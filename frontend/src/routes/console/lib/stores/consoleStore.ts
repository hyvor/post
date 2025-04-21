import { writable } from "svelte/store";
import type { AppConfig } from "../../types";

export const appConfig = writable<AppConfig>();
