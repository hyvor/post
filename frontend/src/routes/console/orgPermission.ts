import type {OrganizationRole} from '@hyvor/design/cloud';
import {authOrganizationStore} from "./lib/stores/consoleStore";
import {get} from "svelte/store";

export function getOrganizationRole(): OrganizationRole {
    return get(authOrganizationStore).role;
}

export function canAccessBilling() {
    const role = getOrganizationRole();
    return role === 'admin' || role === 'billing';
}

export function canAccessSettings() {
    const role = getOrganizationRole();
    return role === 'admin';
}