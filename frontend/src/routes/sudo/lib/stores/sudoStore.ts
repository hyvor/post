import {writable} from 'svelte/store';
import type {Approval, SubscriberImport, SudoConfig} from "../../types";

export const configStore = writable<SudoConfig>();
export const approvalStore = writable<Approval[]>([]);
export const subscriberImportStore = writable<SubscriberImport[]>([]);
