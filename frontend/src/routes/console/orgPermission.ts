import type { OrganizationRole } from '@hyvor/design/cloud';
import { authOrganizationStore } from './lib/stores/consoleStore';
import { get } from 'svelte/store';

function canAccess(allowedRoles: OrganizationRole[]): boolean {
	const role = get(authOrganizationStore)?.role;

	if (role === null) {
		return false;
	}

	return allowedRoles.includes(role);
}

export function canAccessBilling() {
	return canAccess(['admin', 'billing']);
}

export function canAccessSettings() {
	return canAccess(['admin']);
}
