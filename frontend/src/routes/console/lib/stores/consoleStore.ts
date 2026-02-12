import { writable } from 'svelte/store';
import type { AppConfig, Approval, ApprovalStatus } from '../../types';
import type {
	CloudContextOrganization,
	CloudContextUser,
	ResolvedLicense
} from '@hyvor/design/cloud';

export const selectingNewsletter = writable(false);
export const approvalStore = writable<Approval>();
export const userApprovalStatusStore = writable<ApprovalStatus>('pending');
export const authOrganizationStore = writable<CloudContextOrganization>();
export const authUserStore = writable<CloudContextUser>();
export const resolvedLicenseStore = writable<ResolvedLicense>();

let appConfig = {} as AppConfig;

export function setAppConfig(config: AppConfig) {
	appConfig = config;
}

export function getAppConfig() {
	return appConfig;
}
