import { writable } from 'svelte/store';
import type {Approval} from "../../types";

export const approvalStore = writable<Approval[]>([]);
