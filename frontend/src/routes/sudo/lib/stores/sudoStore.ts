import { writable } from 'svelte/store';
import type {Approval, SudoConfig} from "../../types";

export const configStore = writable<SudoConfig>();
export const approvalStore = writable<Approval[]>([]);
