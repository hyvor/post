import {writable} from 'svelte/store';
import type {AppConfig, Approval, ApprovalStatus, AuthUser, Organization, ResolvedLicense} from '../../types';

export const selectingNewsletter = writable(false);
export const approvalStore = writable<Approval>();
export const userApprovalStatusStore = writable<ApprovalStatus>('pending');
export const authUserOrganizationStore = writable<Organization>()
export const authUserStore = writable<AuthUser>()
export const resolvedLicenseStore = writable<ResolvedLicense>()

let appConfig = {} as AppConfig;

export function setAppConfig(config: AppConfig) {
    appConfig = config;
}

export function getAppConfig() {
    return appConfig;
}
