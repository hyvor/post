import { ITEMS_PER_PAGE } from '../generalActions';
import sudoApi from '../sudoApi';
import type { IssueStatus, Issue, IssueSend, SendType } from '../../types';

export const ISSUE_STATUS_FILTERS: Record<IssueStatus, string> = {
	draft: 'Draft',
	scheduled: 'Scheduled',
	sending: 'Sending',
	sent: 'Sent'
};

export function getIssues(
	newsletterId: number | null = null,
	status: IssueStatus | null = null,
	limit: number = ITEMS_PER_PAGE,
	offset: number = 0
) {
	return sudoApi.get<Issue[]>({
		endpoint: 'issues',
		data: {
			newsletter_id: newsletterId,
			status,
			limit,
			offset
		}
	});
}

export function getIssue(id: number) {
	return sudoApi.get<Issue>({
		endpoint: `issues/${id}`
	});
}

export function getIssueSends(
	id: number,
	limit: number = ITEMS_PER_PAGE,
	offset: number = 0,
	search: string = '',
	type: SendType = 'all'
) {
	return sudoApi.get<IssueSend[]>({
		endpoint: `issues/${id}/sends`,
		data: { limit, offset, search, type }
	});
}
