import { ITEMS_PER_PAGE } from '../generalActions';
import sudoApi from '../sudoApi';
import type { IssueStatus, SudoIssue } from '../../types';

export const ISSUE_STATUS_FILTERS: Record<IssueStatus, string> = {
	draft: 'Draft',
	scheduled: 'Scheduled',
	sending: 'Sending',
	sent: 'Sent'
};

export function getIssues(
	subdomain: string | null = null,
	status: IssueStatus | null = null,
	limit: number = ITEMS_PER_PAGE,
	offset: number = 0
) {
	return sudoApi.get<SudoIssue[]>({
		endpoint: 'issues',
		data: {
			subdomain,
			status,
			limit,
			offset
		}
	});
}

export function getIssue(id: number) {
	return sudoApi.get<SudoIssue>({
		endpoint: `issues/${id}`
	});
}
