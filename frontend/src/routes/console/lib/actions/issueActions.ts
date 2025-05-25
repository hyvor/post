import type { Issue, IssueSend, SendType } from "../../types";
import consoleApi from "../consoleApi";

export function createIssueDraft() {
    return consoleApi.post<Issue>({
        endpoint: 'issues',
    });
}

export function deleteIssue(id: number) {
	return consoleApi.delete({
		endpoint: 'issues/' + id
	});
}

export function getIssues(limit: number, offset: number) {
	return consoleApi.get<Issue[]>({
		endpoint: 'issues',
		data: {
			limit: limit,
			offset: offset
		}
	});
}

export function getIssue(id: number) {
    return consoleApi.get<Issue>({
        endpoint: 'issues/' + id
    });
}

export function updateIssue(id: number, updates: Partial<Issue>) {
	return consoleApi.patch<Issue>({
		endpoint: 'issues/' + id,
		data: updates
	});
}

export function sendIssue(id: number) {
	return consoleApi.post<Issue>({
		endpoint: `issues/${id}/send`
	});
}

export function previewIssue(id: number) {
	return consoleApi.get<{ html: string }>({
		endpoint: `issues/${id}/preview`
	});
}

export function sendIssueTest(id: number, email: string) {
	return consoleApi.post({
		endpoint: `issues/${id}/test`,
		data: {
			email
		}
	});
}

export function getIssueProgress(id: number) {
	return consoleApi.get<{ total: number; pending: number; sent: number; progress: number }>({
		endpoint: `issues/${id}/progress`
	});
}

export function getIssueSends(
	id: number,
	limit: number,
	offset: number,
	search: string | null,
	type: SendType
) {
	return consoleApi.get<IssueSend[]>({
		endpoint: `issues/${id}/sends`,
		data: {
			limit,
			offset,
			search,
			type
		}
	});
}

export interface IssueCounts {
	total: number;
	sent: number;
	failed: number;
	pending: number;
	opened: number;
	clicked: number;
	unsubscribed: number;
	bounced: number;
	complained: number;
}

export function getIssueReport(id: number) {
	return consoleApi.get<{ counts: IssueCounts }>({
		endpoint: `issues/${id}/report`
	});
}