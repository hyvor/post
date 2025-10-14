import { writable } from "svelte/store";
import type { AppConfig, Approval, ApprovalStatus } from "../../types";

export const selectingNewsletter = writable(false);
export const approvalStore = writable<Approval>();

export const userApprovalStatusStore = writable<ApprovalStatus>('pending');

let appConfig = {} as AppConfig;

export function setAppConfig(config: AppConfig) {
    appConfig = config;
}

export function getAppConfig() {
    return appConfig;
}