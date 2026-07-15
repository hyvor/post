import { writable } from 'svelte/store';
import type { AppConfig } from '../../types';
import type {
	CloudContextOrganization,
	CloudContextUser,
	ResolvedLicense
} from '@hyvor/design/cloud';

export const selectingNewsletter = writable(false);
export const authOrganizationStore = writable<CloudContextOrganization>();
export const authUserStore = writable<CloudContextUser>();
export const resolvedLicenseStore = writable<ResolvedLicense>();

// trial licenses cannot be used to send issues; a paid license (subscription
// or enterprise contract) is required
export function canSendIssues(license: ResolvedLicense | undefined): boolean {
	return !!license?.license && license.type !== 'trial';
}

let appConfig = {} as AppConfig;

export function setAppConfig(config: AppConfig) {
	appConfig = config;
}

export function getAppConfig() {
	return appConfig;
}
